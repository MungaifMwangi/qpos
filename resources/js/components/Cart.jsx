import axios from "axios";
import React from "react";
import toast, { Toaster } from "react-hot-toast";
import Swal from "sweetalert2";
import SuccessSound from "../sounds/beep-07a.mp3";
import WarningSound from "../sounds/beep-02.mp3";
import playSound from "../utils/playSound";

export default function Cart({ carts, setCartUpdated, cartUpdated }) {
    function increment(id) {
        axios.put("/admin/cart/increment", { id })
            .then((res) => { setCartUpdated(!cartUpdated); playSound(SuccessSound); toast.success(res?.data?.message); })
            .catch((err) => { playSound(WarningSound); toast.error(err.response.data.message); });
    }

    function decrement(id) {
        axios.put("/admin/cart/decrement", { id })
            .then((res) => { setCartUpdated(!cartUpdated); playSound(SuccessSound); toast.success(res?.data?.message); })
            .catch((err) => { playSound(WarningSound); toast.error(err.response.data.message); });
    }

    function destroy(id) {
        Swal.fire({
            title: "Remove this item?",
            showDenyButton: true,
            confirmButtonText: "Yes",
            denyButtonText: "No",
            customClass: { actions: "my-actions", cancelButton: "order-1 right-gap", confirmButton: "order-2", denyButton: "order-3" },
        }).then((result) => {
            if (result.isConfirmed) {
                axios.put("/admin/cart/delete", { id })
                    .then((res) => { setCartUpdated(!cartUpdated); playSound(SuccessSound); toast.success(res?.data?.message); })
                    .catch((err) => { toast.error(err.response.data.message); });
            }
        });
    }

    return (
        <div className="responsive-table">
            <table className="table cart-table" style={{ margin: 0 }}>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style={{ textAlign: "center" }}>Qty</th>
                        <th></th>
                        <th style={{ textAlign: "right" }}>Price</th>
                        <th style={{ textAlign: "right" }}>Total</th>
                    </tr>
                </thead>
                <tbody>
                    {carts.map((item) => (
                        <tr key={item.id}>
                            <td style={{ fontWeight: "600", fontSize: "13px", maxWidth: "130px" }}>
                                <div style={{ overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
                                    {item.product.name}
                                </div>
                            </td>
                            <td style={{ textAlign: "center" }}>
                                <div style={{ display: "inline-flex", alignItems: "center", gap: "4px" }}>
                                    <button className="pos-dec-btn" onClick={() => decrement(item.id)}>
                                        <i className="fas fa-minus"></i>
                                    </button>
                                    <input type="number" className="form-control form-control-sm qty" value={item.quantity} disabled
                                        style={{ width: "42px", textAlign: "center", fontSize: "13px", fontWeight: "700", borderRadius: "6px", padding: "2px 4px" }} />
                                    <button className="pos-inc-btn" onClick={() => increment(item.id)}>
                                        <i className="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                            <td style={{ textAlign: "center" }}>
                                <button className="pos-del-btn" onClick={() => destroy(item.id)}>
                                    <i className="fas fa-trash-alt"></i>
                                </button>
                            </td>
                            <td style={{ textAlign: "right", fontSize: "13px", color: "#666" }}>
                                {item?.product?.discounted_price}
                            </td>
                            <td style={{ textAlign: "right", fontWeight: "700", fontSize: "13px" }}>
                                {item?.row_total}
                            </td>
                        </tr>
                    ))}
                    {carts.length === 0 && (
                        <tr>
                            <td colSpan="5" style={{ textAlign: "center", padding: "24px", color: "#999", fontSize: "12px" }}>
                                <i className="fas fa-shopping-cart" style={{ fontSize: "20px", marginBottom: "6px", display: "block", opacity: "0.3" }}></i>
                                Cart is empty
                            </td>
                        </tr>
                    )}
                </tbody>
            </table>
        </div>
    );
}
