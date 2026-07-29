import React, {useEffect, useState, useCallback } from "react";
import axios from "axios";
import Swal from "sweetalert2";
import Cart from "./Cart";
import toast, { Toaster } from "react-hot-toast";
import CustomerSelect from "./CutomerSelect";

import SuccessSound from "../sounds/beep-07a.mp3";
import WarningSound from "../sounds/beep-02.mp3";
import playSound from "../utils/playSound";

function getErrorMessage(err, fallback = "Something went wrong. Please try again.") {
    const data = err?.response?.data;
    if (data?.errors && typeof data.errors === "object") {
        const messages = Object.values(data.errors).flat().filter(Boolean);
        if (messages.length) return messages.join("\n");
    }
    if (data?.message) return data.message;
    return err?.message || fallback;
}

export default function Pos() {
    const [products, setProducts] = useState([]);
    const [carts, setCarts] = useState([]);
    const [orderDiscount, setOrderDiscount] = useState(0);
    const [paid, setPaid] = useState(0);
    const [due, setDue] = useState(0);
    const [change, setChange] = useState(0);
    const [total, setTotal] = useState(0);
    const [updateTotal, setUpdateTotal] = useState(0);
    const [customerId, setCustomerId] = useState();
    const [paymentMethod, setPaymentMethod] = useState("cash");
    const [mpesaCode, setMpesaCode] = useState("");
    const [taxMode, setTaxMode] = useState("exclusive");
    const [cartUpdated, setCartUpdated] = useState(false);
    const [productUpdated, setProductUpdated] = useState(false);
    const [searchQuery, setSearchQuery] = useState("");
    const [searchBarcode, setSearchBarcode] = useState("");
    const { protocol, hostname, port } = window.location;
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(0);
    const [loading, setLoading] = useState(false);
    const fullDomainWithPort = `${protocol}//${hostname}${port ? `:${port}` : ""}`;

    const getProducts = useCallback(async (search = "", page = 1, barcode = "") => {
        setLoading(true);
        try {
            const res = await axios.get('/admin/get/products', { params: { search, page, barcode } });
            const productsData = res.data;
            setProducts((prev) => [...prev, ...productsData.data]);
            if (productsData.data.length === 1 && barcode != "") {
                addProductToCart(productsData.data[0].id);
                getCarts();
            }
            setTotalPages(productsData.meta.last_page);
        } catch (error) {
            console.error("Error fetching products:", error);
        } finally {
            setLoading(false);
        }
    }, []);

    const getUpdatedProducts = useCallback(async () => {
        try {
            const res = await axios.get('/admin/get/products');
            const productsData = res.data;
            setProducts(productsData.data);
            setTotalPages(productsData.meta.last_page);
        } catch (error) {
            console.error("Error fetching products:", error);
        }
    }, []);

    useEffect(() => { getUpdatedProducts(); }, [productUpdated]);

    const getCarts = async () => {
        try {
            const res = await axios.get('/admin/cart');
            const data = res.data;
            setTotal(data?.total);
            setUpdateTotal(data?.total - orderDiscount);
            setCarts(data?.carts);
        } catch (error) {
            console.error("Error fetching carts:", error);
        }
    };

    useEffect(() => { getCarts(); }, []);
    useEffect(() => { getCarts(); }, [cartUpdated]);

    useEffect(() => {
        let paid1 = paid;
        let disc = orderDiscount;
        if (paid == "") paid1 = 0;
        if (orderDiscount == "") disc = 0;
        const netAmount = parseFloat(total) - parseFloat(disc);
        const vatRate = 0.16;
        const grossAmount = taxMode === "exclusive" ? parseFloat((netAmount * (1 + vatRate)).toFixed(2)) : netAmount;
        const balance = grossAmount - parseFloat(paid1);
        setUpdateTotal(grossAmount?.toFixed(2));
        setDue((balance > 0 ? balance : 0).toFixed(2));
        setChange((balance < 0 ? -balance : 0).toFixed(2));
    }, [orderDiscount, paid, total, taxMode]);

    useEffect(() => {
        if (searchQuery) { setProducts([]); getProducts(searchQuery, currentPage, ""); }
        setSearchBarcode("");
    }, [currentPage, searchQuery]);

    useEffect(() => {
        if (searchBarcode) { setProducts([]); getProducts("", currentPage, searchBarcode); }
    }, [searchBarcode]);

    useEffect(() => {
        if (!document.getElementById('cart')) return;
        const handleScroll = () => {
            if (window.innerHeight + document.documentElement.scrollTop >= document.documentElement.offsetHeight) {
                if (currentPage < totalPages) setCurrentPage((prev) => prev + 1);
            }
        };
        window.addEventListener("scroll", handleScroll);
        return () => { window.removeEventListener("scroll", handleScroll); };
    }, [currentPage, totalPages]);

    function addProductToCart(id) {
        playSound(SuccessSound);
        axios.post("/admin/cart", { id })
            .then((res) => { getCarts(); toast.success(res?.data?.message); })
            .catch((err) => { playSound(WarningSound); toast.error(getErrorMessage(err)); });
    }

    function cartEmpty() {
        if (total <= 0) return;
        Swal.fire({
            title: "Are you sure you want to clear the cart?",
            showDenyButton: true,
            confirmButtonText: "Yes",
            denyButtonText: "No",
            customClass: { actions: "my-actions", cancelButton: "order-1 right-gap", confirmButton: "order-2", denyButton: "order-3" },
        }).then((result) => {
            if (result.isConfirmed) {
                axios.put("/admin/cart/empty")
                    .then((res) => { setCartUpdated(!cartUpdated); playSound(SuccessSound); toast.success(res?.data?.message); })
                    .catch((err) => { playSound(WarningSound); toast.error(getErrorMessage(err)); });
            }
        });
    }

    function orderCreate() {
        if (total <= 0) return;
        if (!customerId) { toast.error("Please select customer"); return; }
        if (paymentMethod === "cash" && parseFloat(paid || 0) < parseFloat(updateTotal || 0)) { toast.error("Cash tendered must cover the total."); return; }
        if (paymentMethod === "debtor" && customerId === 1) { toast.error("Select a registered customer for a credit sale."); return; }
        if (paymentMethod === "stk_push" && !mpesaCode) { toast.error("Enter the M-Pesa transaction code."); return; }
        const balanceLine = parseFloat(change) > 0 ? `Change: KES ${change}` : `Due: KES ${due}`;
        Swal.fire({
            title: `Complete this order? <br><small>${balanceLine}</small>`,
            showDenyButton: true,
            confirmButtonText: "Yes",
            denyButtonText: "No",
            customClass: { actions: "my-actions", cancelButton: "order-1 right-gap", confirmButton: "order-2", denyButton: "order-3" },
        }).then((result) => {
            if (result.isConfirmed) {
                // Open hidden popup while user gesture is active
                var printWindow = window.open('', 'receipt', 'width=340,height=600,left=200,top=100');

                axios.put("/admin/order/create", {
                    customer_id: customerId,
                    order_discount: parseFloat(orderDiscount) || 0,
                    paid: parseFloat(paid) || 0,
                    payment_method: paymentMethod,
                    customer_phone: mpesaCode,
                    mpesa_code: mpesaCode,
                    tax_mode: taxMode,
                })
                .then((res) => {
                    setCartUpdated(!cartUpdated);
                    setProductUpdated(!productUpdated);
                    toast.success(res?.data?.message);
                    var orderId = res?.data?.order?.id;
                    if (orderId && printWindow && !printWindow.closed) {
                        printWindow.location.href = '/admin/orders/print-receipt/' + orderId;
                    }
                })
                .catch((err) => {
                    if (printWindow && !printWindow.closed) printWindow.close();
                    playSound(WarningSound); toast.error(getErrorMessage(err), { duration: 6000 });
                });
            }
        });
    }

    const formatKES = (val) => Number(val).toLocaleString("en-KE", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    return (
        <>
            {/* Page Header */}
            <div style={{ display: "flex", alignItems: "center", gap: "12px", marginBottom: "16px" }}>
                <div style={{ display: "inline-flex", alignItems: "center", justifyContent: "center", width: "42px", height: "42px", borderRadius: "10px", background: "linear-gradient(135deg,#1a7a4e,#28a745)", color: "#fff", fontSize: "18px", flexShrink: 0 }}>
                    <i className="fas fa-cart-plus"></i>
                </div>
                <div style={{ flex: 1 }}>
                    <h2 style={{ margin: 0, fontSize: "20px", fontWeight: "700", color: "#303030" }}>Point of Sale</h2>
                    <p style={{ margin: 0, fontSize: "12px", color: "#999" }}>Scan, add products, and process checkout</p>
                </div>
            </div>

            <div style={{ display: "flex", gap: "16px", alignItems: "flex-start" }}>
                {/* Left: Cart + Checkout */}
                <div style={{ width: "42%", flexShrink: 0 }}>
                    {/* Customer */}
                    <div className="pos-cart-section">
                        <div className="pos-cart-header">
                            <i className="fas fa-user" style={{ marginRight: "6px", color: "#999" }}></i> Customer
                        </div>
                        <div style={{ padding: "12px 16px" }}>
                            <CustomerSelect setCustomerId={setCustomerId} />
                        </div>
                    </div>

                    {/* Cart */}
                    <div className="pos-cart-section">
                        <div className="pos-cart-header">
                            <i className="fas fa-shopping-cart" style={{ marginRight: "6px", color: "#999" }}></i> Cart Items
                        </div>
                        <Cart carts={carts} setCartUpdated={setCartUpdated} cartUpdated={cartUpdated} />
                    </div>

                    {/* Checkout Summary */}
                    <div className="pos-cart-section">
                        <div className="pos-cart-header">
                            <i className="fas fa-receipt" style={{ marginRight: "6px", color: "#999" }}></i> Checkout
                        </div>
                        <div style={{ padding: "12px 16px" }}>
                            <div className="pos-summary-row">
                                <span className="pos-summary-label">Sub Total</span>
                                <span className="pos-summary-value">KES {formatKES(total)}</span>
                            </div>

                            <div className="pos-summary-row">
                                <span className="pos-summary-label">Payment</span>
                                <select className="form-control form-control-sm" style={{ width: "55%", borderRadius: "8px", fontSize: "14px", padding: "6px 10px" }} value={paymentMethod} onChange={(e) => setPaymentMethod(e.target.value)} disabled={total <= 0}>
                                    <option value="cash">Cash</option>
                                    <option value="stk_push">M-Pesa</option>
                                    <option value="debtor">Customer Credit</option>
                                </select>
                            </div>

                            <div className="pos-summary-row">
                                <span className="pos-summary-label">VAT Mode</span>
                                <select className="form-control form-control-sm" style={{ width: "55%", borderRadius: "8px", fontSize: "14px", padding: "6px 10px" }} value={taxMode} onChange={(e) => setTaxMode(e.target.value)} disabled={total <= 0}>
                                    <option value="exclusive">Exclusive (16%)</option>
                                    <option value="inclusive">Inclusive (16%)</option>
                                </select>
                            </div>

                            {paymentMethod === "stk_push" && (
                                <div className="pos-summary-row">
                                    <span className="pos-summary-label">M-Pesa Code</span>
                                    <input type="text" className="form-control form-control-sm" style={{ width: "55%", borderRadius: "8px", fontSize: "14px", padding: "6px 10px" }} placeholder="e.g. QJH7B3K9XY" value={mpesaCode} onChange={(e) => setMpesaCode(e.target.value)} />
                                </div>
                            )}

                            <div className="pos-summary-row">
                                <span className="pos-summary-label">Discount</span>
                                <input type="number" className="form-control form-control-sm" style={{ width: "55%", borderRadius: "8px", fontSize: "14px", padding: "6px 10px" }} placeholder="0.00" min={0} disabled={total <= 0} value={orderDiscount}
                                    onChange={(e) => {
                                        const value = e.target.value;
                                        if (parseFloat(value) > total || parseFloat(value) < 0) return;
                                        setOrderDiscount(value);
                                    }}
                                />
                            </div>

                            {paymentMethod === "cash" && (
                                <div className="pos-summary-row">
                                    <span className="pos-summary-label" style={{ fontSize: "11px" }}>Round Down</span>
                                    <input type="checkbox" style={{ width: "16px", height: "16px", accentColor: "#1a7a4e" }} disabled={total <= 0}
                                        onChange={(e) => {
                                            if (e.target.checked) { setOrderDiscount((total % 1)?.toFixed(2)); }
                                            else { setOrderDiscount(0); }
                                        }}
                                    />
                                </div>
                            )}

                            <div className="pos-summary-row" style={{ borderTop: "1px solid #f0f0f0", paddingTop: "8px", marginTop: "4px" }}>
                                <span className="pos-summary-label" style={{ fontWeight: "700", color: "#303030" }}>Total</span>
                                <span style={{ fontWeight: "800", fontSize: "17px", color: "#d35400" }}>KES {formatKES(updateTotal)}</span>
                            </div>

                            {paymentMethod === "cash" && (
                                <div className="pos-summary-row">
                                    <span className="pos-summary-label">Paid</span>
                                    <input type="number" className="form-control form-control-sm" style={{ width: "55%", borderRadius: "8px", fontSize: "14px", padding: "6px 10px" }} placeholder="0.00" min={0} disabled={total <= 0} value={paid}
                                        onChange={(e) => { if (parseFloat(e.target.value) < 0) return; setPaid(e.target.value); }}
                                    />
                                </div>
                            )}

                            <div className="pos-summary-row">
                                <span className="pos-summary-label">Due</span>
                                <span className="pos-summary-value" style={{ color: "#dc2626" }}>
                                    {paymentMethod === "debtor" ? `KES ${formatKES(updateTotal)}` : (paymentMethod === "stk_push" ? `KES ${formatKES(updateTotal)}` : `KES ${formatKES(due)}`)}
                                </span>
                            </div>

                            {parseFloat(change) > 0 && (
                                <div className="pos-summary-row" style={{ color: "#1a7a4e" }}>
                                    <span style={{ fontWeight: "700" }}>Change</span>
                                    <span style={{ fontWeight: "800", fontSize: "16px" }}>KES {formatKES(change)}</span>
                                </div>
                            )}

                            <div style={{ display: "flex", gap: "10px", marginTop: "14px" }}>
                                <button onClick={() => cartEmpty()} type="button" className="pos-btn-clear">
                                    <i className="fas fa-trash-alt"></i> Clear
                                </button>
                                <button onClick={() => orderCreate()} type="button" className="pos-btn-checkout">
                                    <i className="fas fa-check-circle"></i> Checkout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Right: Products */}
                <div style={{ flex: 1, minWidth: 0 }}>
                    {/* Search */}
                    <div style={{ display: "flex", gap: "10px", marginBottom: "12px" }}>
                        <div style={{ flex: "0 0 200px", position: "relative" }}>
                            <i className="fas fa-barcode" style={{ position: "absolute", left: "12px", top: "50%", transform: "translateY(-50%)", color: "#999", fontSize: "13px" }}></i>
                            <input type="text" className="pos-search-input" style={{ paddingLeft: "34px" }} placeholder="Scan barcode..." value={searchBarcode} autoFocus onChange={(e) => setSearchBarcode(e.target.value)} />
                        </div>
                        <div style={{ flex: 1, position: "relative" }}>
                            <i className="fas fa-search" style={{ position: "absolute", left: "12px", top: "50%", transform: "translateY(-50%)", color: "#999", fontSize: "13px" }}></i>
                            <input type="text" className="pos-search-input" style={{ paddingLeft: "34px" }} placeholder="Search products by name..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} />
                        </div>
                    </div>

                    {/* Products Grid */}
                    <div className="products-card-container">
                        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(140px, 1fr))", gap: "10px" }}>
                            {products.length > 0 && products.map((product, index) => (
                                <div key={index} className="product-card-item" onClick={() => addProductToCart(product.id)}>
                                    <div style={{ textAlign: "center", marginBottom: "6px" }}>
                                        <img
                                            src={`${fullDomainWithPort}/storage/${product.image}`}
                                            alt={product.name}
                                            style={{ width: "100%", height: "80px", objectFit: "contain", borderRadius: "8px" }}
                                            onError={(e) => { e.target.onerror = null; e.target.src = `${fullDomainWithPort}/assets/images/no-image.png`; }}
                                        />
                                    </div>
                                    <div className="product-details">
                                        <p className="product-name">{product.name}</p>
                                        <p style={{ color: "#d35400", fontWeight: "700", fontSize: "13px" }}>KES {product?.discounted_price}</p>
                                        <p style={{ fontSize: "12px", color: product.quantity > 0 ? "#28a745" : "#dc2626" }}>
                                            Stock: {product.quantity}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                        {products.length === 0 && !loading && (
                            <div style={{ textAlign: "center", padding: "40px 20px", color: "#999" }}>
                                <i className="fas fa-box-open" style={{ fontSize: "32px", marginBottom: "8px", display: "block", opacity: "0.4" }}></i>
                                <div style={{ fontSize: "13px" }}>No products found. Search above or scan a barcode.</div>
                            </div>
                        )}
                        {loading && <div className="loading-more"><i className="fas fa-spinner fa-spin mr-1"></i> Loading more products...</div>}
                    </div>
                </div>
            </div>

            <Toaster position="top-right" reverseOrder={false} />
        </>
    );
}
