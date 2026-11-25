"""
併發扣庫存（預設不執行：pytest -m race）

舊系統：MyISAM 沒有交易，扣庫存是「讀出 → PHP 算 → 寫回」，同時下單會超賣或少扣
新系統：DynamoDB TransactWriteItems + 條件式更新，成功筆數 = 扣掉的數量，庫存不會變負數
"""

from concurrent.futures import ThreadPoolExecutor

import pytest

from conftest import Client, Shop, is_legacy, reset_data

pytestmark = pytest.mark.race


@pytest.mark.legacy_bug("BUG-02")
def test_RACE01_concurrent_orders_on_low_stock(shop):
    product_id, stock = 5, 5
    attempts = 30
    rounds = 10 if is_legacy() else 1  # 舊系統的競態不一定每次出現，多試幾輪

    def buy(_):
        return Shop(Client()).create_order(1, f"{product_id}:1")

    for n in range(rounds):
        if n:
            reset_data()
        with ThreadPoolExecutor(max_workers=attempts) as ex:
            results = list(ex.map(buy, range(attempts)))
        ok = sum(1 for r in results if r.ok)
        final = shop.stock(product_id)
        consistent = ok <= stock and final == stock - ok
        if is_legacy() and not consistent:
            break

    if is_legacy():
        if consistent:
            pytest.skip(f"{rounds} 輪都沒有重現（成功 {ok} 筆、庫存剩 {final}），再跑一次通常就會出現")
        print(f"重現超賣/少扣：成功 {ok} 筆，庫存 {stock} → {final}")
    else:
        assert consistent, f"成功 {ok} 筆，庫存剩 {final}"
        assert final >= 0
