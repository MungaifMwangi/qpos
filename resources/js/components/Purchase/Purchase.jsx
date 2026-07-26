import React, { useCallback, useEffect, useState } from "react";
import Suppliers from "./Suppliers";
import axios from "axios";
import Swal from "sweetalert2";
import toast, { Toaster } from "react-hot-toast";
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";

const styles = {
    header: {
        display: "flex",
        alignItems: "center",
        gap: "12px",
        marginBottom: "20px",
    },
    headerIcon: {
        display: "inline-flex",
        alignItems: "center",
        justifyContent: "center",
        width: "42px",
        height: "42px",
        borderRadius: "10px",
        background: "linear-gradient(135deg,#d35400,#e67e22)",
        color: "#fff",
        fontSize: "18px",
        flexShrink: "0",
    },
    headerText: {
        flex: "1",
    },
    headerTitle: {
        margin: 0,
        fontSize: "20px",
        fontWeight: "700",
        color: "#303030",
    },
    headerSubtitle: {
        margin: 0,
        fontSize: "12px",
        color: "#999",
    },
    section: {
        background: "#fff",
        border: "1px solid #e8e8e8",
        borderRadius: "14px",
        marginBottom: "16px",
        position: "relative",
    },
    sectionHeader: {
        padding: "16px 20px 8px",
        fontWeight: "700",
        color: "#303030",
        fontSize: "13px",
        margin: 0,
    },
    sectionBody: {
        padding: "0 20px 16px",
    },
    grid2: {
        display: "grid",
        gridTemplateColumns: "1fr 1fr",
        gap: "16px",
    },
    grid3: {
        display: "grid",
        gridTemplateColumns: "1fr 1fr 1fr",
        gap: "14px",
    },
    label: {
        display: "block",
        fontSize: "12px",
        fontWeight: "600",
        color: "#666",
        marginBottom: "6px",
    },
    input: {
        width: "100%",
        padding: "8px 12px",
        border: "1px solid #e0e0e0",
        borderRadius: "8px",
        fontSize: "13px",
        outline: "none",
        transition: "border-color 0.2s",
    },
    inputFocus: {
        borderColor: "#d35400",
    },
    searchRow: {
        display: "flex",
        gap: "10px",
        alignItems: "center",
        marginBottom: "12px",
    },
    searchInput: {
        flex: "1",
        padding: "10px 14px",
        border: "1px solid #e0e0e0",
        borderRadius: "10px",
        fontSize: "14px",
        outline: "none",
    },
    searchBtn: {
        display: "inline-flex",
        alignItems: "center",
        gap: "6px",
        padding: "10px 20px",
        background: "linear-gradient(135deg,#d35400,#e67e22)",
        color: "#fff",
        border: "none",
        borderRadius: "10px",
        fontSize: "13px",
        fontWeight: "600",
        cursor: "pointer",
        whiteSpace: "nowrap",
    },
    resultsDropdown: {
        maxHeight: "200px",
        overflowY: "auto",
        border: "1px solid #e8e8e8",
        borderRadius: "10px",
        marginBottom: "12px",
        background: "#fff",
    },
    resultItem: {
        padding: "10px 14px",
        cursor: "pointer",
        fontSize: "13px",
        borderBottom: "1px solid #f5f5f5",
        display: "flex",
        justifyContent: "space-between",
        alignItems: "center",
        transition: "background 0.15s",
    },
    table: {
        width: "100%",
        borderCollapse: "collapse",
    },
    th: {
        fontSize: "11px",
        fontWeight: "700",
        textTransform: "uppercase",
        letterSpacing: "0.5px",
        color: "#999",
        padding: "10px 12px",
        borderBottom: "1px solid #e8e8e8",
        textAlign: "center",
    },
    td: {
        padding: "10px 12px",
        borderBottom: "1px solid #f5f5f5",
        fontSize: "13px",
        textAlign: "center",
        color: "#303030",
    },
    qtyInput: {
        width: "70px",
        padding: "6px 8px",
        border: "1px solid #e0e0e0",
        borderRadius: "6px",
        fontSize: "13px",
        textAlign: "center",
        outline: "none",
    },
    priceInput: {
        width: "100px",
        padding: "6px 8px",
        border: "1px solid #e0e0e0",
        borderRadius: "6px",
        fontSize: "13px",
        textAlign: "right",
        outline: "none",
    },
    deleteBtn: {
        display: "inline-flex",
        alignItems: "center",
        justifyContent: "center",
        width: "28px",
        height: "28px",
        borderRadius: "6px",
        background: "#fee2e2",
        color: "#dc2626",
        border: "none",
        cursor: "pointer",
        fontSize: "11px",
    },
    summaryTable: {
        width: "100%",
        borderCollapse: "collapse",
    },
    summaryRow: {
        display: "flex",
        justifyContent: "space-between",
        padding: "6px 0",
        fontSize: "13px",
    },
    summaryLabel: {
        color: "#999",
    },
    summaryValue: {
        fontWeight: "600",
        color: "#303030",
    },
    grandTotalRow: {
        display: "flex",
        justifyContent: "space-between",
        padding: "10px 0 0",
        marginTop: "6px",
        borderTop: "1px solid #e8e8e8",
        fontSize: "18px",
        fontWeight: "800",
        color: "#d35400",
    },
    submitBtn: {
        display: "inline-flex",
        alignItems: "center",
        gap: "8px",
        padding: "12px 32px",
        background: "linear-gradient(135deg,#d35400,#e67e22)",
        color: "#fff",
        border: "none",
        borderRadius: "10px",
        fontSize: "14px",
        fontWeight: "700",
        cursor: "pointer",
        transition: "transform 0.15s",
    },
    emptyState: {
        padding: "40px 20px",
        textAlign: "center",
        color: "#999",
        fontSize: "13px",
    },
    emptyIcon: {
        fontSize: "32px",
        marginBottom: "8px",
        display: "block",
        opacity: "0.4",
    },
};

export default function Purchase() {
    const [searchTerm, setSearchTerm] = useState("");
    const [barcode, setBarcode] = useState("");
    const [selectedSupplier, setSelectedSupplier] = useState({
        value: 1,
        label: "Own Supplier",
    });
    const [purchaseId, setPurchaseId] = useState(null);
    const [date, setDate] = useState(null);
    const [supplierId, setSupplierId] = useState(null);
    const [tax, setTax] = useState(0);
    const [discount, setDiscount] = useState(0);
    const [shipping, setShipping] = useState(0);
    const [products, setProducts] = useState([]);
    const [searchResults, setSearchResults] = useState([]);

    useEffect(() => {
        const searchParams = new URLSearchParams(window.location.search);
        const barcodeParam = searchParams.get("barcode");
        const purchase_id = searchParams.get("purchase_id");
        if (barcodeParam) {
            setSearchTerm(barcodeParam);
            setBarcode(barcodeParam);
        }
        if (purchase_id) {
            setPurchaseId(purchase_id);
        }
    }, []);

    useEffect(() => {
        if (barcode) {
            getProducts();
        }
    }, [barcode]);

    useEffect(() => {
        if (purchaseId) {
            getPurchaseProducts();
        }
    }, [purchaseId]);

    const getPurchaseProducts = useCallback(async () => {
        try {
            const res = await axios.get(`/admin/purchase/${purchaseId}`);
            const purchaseData = res.data;
            const purchaseProducts = purchaseData?.items?.map((item) => ({
                item_id: item.id,
                id: item.product_id,
                name: item.name,
                price: item.price,
                purchase_price: item.purchase_price,
                stock: item.stock,
                qty: item.quantity,
                subTotal: item.purchase_price * item.quantity,
            }));
            setProducts(purchaseProducts);
            setDate(purchaseData?.date ? purchaseData.date.split(" ")[0] : "");
            setSelectedSupplier({
                value: purchaseData?.supplier_id,
                label: purchaseData?.supplier?.name,
            });
            setTax(purchaseData?.tax);
            setDiscount(purchaseData?.discount_value);
            setShipping(purchaseData?.shipping);
        } catch (error) {
            console.error("Error fetching products:", error);
        }
    }, [purchaseId]);

    const getProducts = useCallback(async () => {
        if (!searchTerm.trim()) return;
        try {
            const res = await axios.get("/admin/products", {
                params: { search: searchTerm },
            });
            const productsData = res.data;
            if (productsData?.data && productsData.data.length) {
                productsData.data.forEach((product) => {
                    const existingProductIndex = products.findIndex(
                        (p) => p.id === product.id
                    );
                    if (existingProductIndex !== -1) {
                        setProducts((prevProducts) => {
                            const updatedProducts = [...prevProducts];
                            updatedProducts[existingProductIndex].qty += 1;
                            updatedProducts[existingProductIndex].subTotal =
                                updatedProducts[existingProductIndex].purchase_price *
                                updatedProducts[existingProductIndex].qty;
                            return updatedProducts;
                        });
                    } else {
                        const newProduct = {
                            id: product.id,
                            name: product.name,
                            price: product.price,
                            purchase_price: product.purchase_price,
                            stock: product.quantity,
                            qty: 1,
                            subTotal: product.purchase_price,
                        };
                        setProducts((prevProducts) => [...prevProducts, newProduct]);
                    }
                });
            }
        } catch (error) {
            console.error("Error fetching products:", error);
        } finally {
            setSearchTerm("");
        }
    }, [searchTerm]);

    const handleDelete = (id) => {
        setProducts(products.filter((product) => product.id !== id));
    };

    const handleQtyChange = (id, value) => {
        const updatedProducts = products.map((product) => {
            if (product.id === id) {
                const newQty = parseInt(value) || 0;
                return {
                    ...product,
                    qty: newQty,
                    subTotal: parseFloat((product.purchase_price * newQty).toFixed(2)),
                };
            }
            return product;
        });
        setProducts(updatedProducts);
    };

    const handlePriceChange = (id, value) => {
        const updatedProducts = products.map((product) => {
            if (product.id === id) {
                const newPrice = parseFloat(value) || 0;
                return {
                    ...product,
                    purchase_price: newPrice,
                    subTotal: parseFloat((product.qty * newPrice).toFixed(2)),
                };
            }
            return product;
        });
        setProducts(updatedProducts);
    };

    const handleSearchAdd = () => {
        getProducts();
    };

    const calculateTotals = () => {
        const subTotal = products.reduce((sum, product) => sum + product.subTotal, 0);
        const formattedSubTotal = parseFloat(subTotal.toFixed(2));
        const formattedTax = parseFloat((tax || 0).toFixed(2));
        const formattedDiscount = parseFloat((discount || 0).toFixed(2));
        const formattedShipping = parseFloat((shipping || 0).toFixed(2));
        const grandTotal = parseFloat(
            (formattedSubTotal + formattedTax - formattedDiscount + formattedShipping).toFixed(2)
        );
        return {
            subTotal: formattedSubTotal,
            tax: formattedTax,
            discount: formattedDiscount,
            shipping: formattedShipping,
            grandTotal,
        };
    };

    const totals = calculateTotals();

    const handleSubmit = async () => {
        if (totals.grandTotal <= 0) return;
        if (!date) {
            toast.error("Please select purchase date.");
            return;
        }
        if (!supplierId) {
            toast.error("Please select a supplier.");
            return;
        }

        Swal.fire({
            title: `Are you sure you want to save this purchase?`,
            showDenyButton: true,
            confirmButtonText: "Yes",
            denyButtonText: "No",
            customClass: {
                actions: "my-actions",
                cancelButton: "order-1 right-gap",
                confirmButton: "order-2",
                denyButton: "order-3",
            },
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await axios.post("/admin/purchase", {
                        purchase_id: purchaseId,
                        date,
                        products,
                        supplierId,
                        totals,
                    });
                    setProducts([]);
                    toast.success(res?.data?.message);
                    window.location.href = "/admin/purchase";
                } catch (err) {
                    toast.error(err.response?.data?.message || "An error occurred");
                }
            }
        });
    };

    useEffect(() => {
        async function getProducts() {
            if (!searchTerm.trim()) {
                setSearchResults([]);
                return;
            }
            try {
                const res = await axios.get("/admin/products", {
                    params: { search: searchTerm },
                });
                const productsData = res.data;
                setSearchResults(productsData?.data || []);
            } catch (error) {
                console.error("Error fetching products:", error);
            }
        }
        getProducts();
    }, [searchTerm]);

    const handleProductSelect = (product) => {
        const existingProductIndex = products.findIndex((p) => p.id === product.id);
        if (existingProductIndex !== -1) {
            setProducts((prevProducts) => {
                const updatedProducts = [...prevProducts];
                updatedProducts[existingProductIndex].qty += 1;
                updatedProducts[existingProductIndex].subTotal =
                    updatedProducts[existingProductIndex].purchase_price *
                    updatedProducts[existingProductIndex].qty;
                return updatedProducts;
            });
        } else {
            const newProduct = {
                id: product.id,
                name: product.name,
                price: product.price,
                purchase_price: product.purchase_price,
                stock: product.quantity,
                qty: 1,
                subTotal: product.purchase_price,
            };
            setProducts((prevProducts) => [...prevProducts, newProduct]);
        }
        setSearchTerm("");
        setSearchResults([]);
    };

    const formatKES = (val) => "KES " + Number(val).toLocaleString("en-KE", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    return (
        <>
            {/* Page Header */}
            <div style={styles.header}>
                <div style={styles.headerIcon}>
                    <i className="fas fa-shopping-bag"></i>
                </div>
                <div style={styles.headerText}>
                    <h2 style={styles.headerTitle}>
                        {purchaseId ? `Edit Purchase #${purchaseId}` : "New Purchase"}
                    </h2>
                    <p style={styles.headerSubtitle}>
                        {purchaseId ? "Update purchase details and items" : "Create a new purchase order and receive stock"}
                    </p>
                </div>
                <a
                    href="/admin/purchase"
                    style={{
                        display: "inline-flex",
                        alignItems: "center",
                        gap: "6px",
                        padding: "8px 16px",
                        background: "#f5f5f5",
                        color: "#666",
                        border: "1px solid #e0e0e0",
                        borderRadius: "8px",
                        fontSize: "12px",
                        fontWeight: "600",
                        textDecoration: "none",
                    }}
                >
                    <i className="fas fa-arrow-left"></i> Back to Purchases
                </a>
            </div>

            {/* Date & Supplier */}
            <div style={styles.section}>
                <div style={styles.sectionBody}>
                    <div style={{ ...styles.grid2, marginTop: "16px" }}>
                        <div>
                            <label style={styles.label}>
                                Purchase Date <span style={{ color: "#dc2626" }}>*</span>
                            </label>
                            <DatePicker
                                name="date"
                                className="form-control"
                                placeholderText="Select purchase date"
                                selected={date}
                                dateFormat="yyyy-MM-dd"
                                wrapperClassName="w-100"
                                onChange={(d) => {
                                    const formattedDate = d ? d.toISOString().split("T")[0] : null;
                                    setDate(formattedDate);
                                }}
                                customInput={
                                    <input
                                        style={{ ...styles.input, cursor: "pointer" }}
                                        readOnly
                                    />
                                }
                            />
                        </div>
                        <div>
                            <label style={styles.label}>
                                Supplier <span style={{ color: "#dc2626" }}>*</span>
                            </label>
                            <Suppliers
                                setSupplierId={setSupplierId}
                                oldSupplier={selectedSupplier}
                            />
                        </div>
                    </div>
                </div>
            </div>

            {/* Add Products */}
            <div style={styles.section}>
                <h6 style={styles.sectionHeader}>Add Products</h6>
                <div style={styles.sectionBody}>
                    <div style={styles.searchRow}>
                        <input
                            type="search"
                            style={styles.searchInput}
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            placeholder="Search by product name or barcode..."
                            onKeyDown={(e) => {
                                if (e.key === "Enter") {
                                    e.preventDefault();
                                    handleSearchAdd();
                                }
                            }}
                        />
                        <button style={styles.searchBtn} onClick={handleSearchAdd}>
                            <i className="fas fa-search"></i> Add
                        </button>
                    </div>

                    {searchResults.length > 0 && (
                        <div style={styles.resultsDropdown}>
                            {searchResults.map((product) => (
                                <div
                                    key={product.id}
                                    style={styles.resultItem}
                                    onClick={() => handleProductSelect(product)}
                                    onMouseEnter={(e) =>
                                        (e.currentTarget.style.background = "#f9f9f9")
                                    }
                                    onMouseLeave={(e) =>
                                        (e.currentTarget.style.background = "#fff")
                                    }
                                >
                                    <span style={{ fontWeight: "600" }}>{product.name}</span>
                                    <span style={{ color: "#999", fontSize: "12px" }}>
                                        {formatKES(product.purchase_price)} | Stock: {product.quantity}
                                    </span>
                                </div>
                            ))}
                        </div>
                    )}

                    {/* Products Table */}
                    <div style={{ overflowX: "auto" }}>
                        <table style={styles.table}>
                            <thead>
                                <tr>
                                    <th style={styles.th}>#</th>
                                    <th style={{ ...styles.th, textAlign: "left" }}>Product</th>
                                    <th style={styles.th}>Purchase Price</th>
                                    <th style={styles.th}>Stock</th>
                                    <th style={styles.th}>Qty</th>
                                    <th style={styles.th} className="text-right">Sub Total</th>
                                    <th style={styles.th}></th>
                                </tr>
                            </thead>
                            <tbody>
                                {products.length === 0 ? (
                                    <tr>
                                        <td colSpan="7" style={styles.emptyState}>
                                            <i className="fas fa-box-open" style={styles.emptyIcon}></i>
                                            No products added yet. Search above to add products.
                                        </td>
                                    </tr>
                                ) : (
                                    products.map((product, index) => (
                                        <tr key={product.id}>
                                            <td style={styles.td}>{index + 1}</td>
                                            <td style={{ ...styles.td, textAlign: "left", fontWeight: "600" }}>
                                                {product.name}
                                            </td>
                                            <td style={styles.td}>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    style={styles.priceInput}
                                                    value={product.purchase_price}
                                                    onChange={(e) =>
                                                        handlePriceChange(product.id, e.target.value)
                                                    }
                                                />
                                            </td>
                                            <td style={{ ...styles.td, color: "#999" }}>
                                                {product.stock}
                                            </td>
                                            <td style={styles.td}>
                                                <input
                                                    type="number"
                                                    min="1"
                                                    style={styles.qtyInput}
                                                    value={product.qty}
                                                    onChange={(e) =>
                                                        handleQtyChange(product.id, e.target.value)
                                                    }
                                                />
                                            </td>
                                            <td style={{ ...styles.td, textAlign: "right", fontWeight: "700" }}>
                                                {formatKES(product.subTotal)}
                                            </td>
                                            <td style={styles.td}>
                                                <button
                                                    style={styles.deleteBtn}
                                                    title="Remove"
                                                    onClick={() => handleDelete(product.id)}
                                                >
                                                    <i className="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* Tax / Discount / Shipping + Summary */}
            <div style={styles.grid2}>
                {/* Adjustments */}
                <div style={styles.section}>
                    <h6 style={styles.sectionHeader}>Adjustments</h6>
                    <div style={styles.sectionBody}>
                        <div style={{ ...styles.grid3, marginTop: "8px" }}>
                            <div>
                                <label style={styles.label}>Tax</label>
                                <input
                                    type="number"
                                    style={styles.input}
                                    value={tax}
                                    min="0"
                                    onChange={(e) => setTax(parseFloat(e.target.value) || 0)}
                                    placeholder="0.00"
                                />
                            </div>
                            <div>
                                <label style={styles.label}>Discount</label>
                                <input
                                    type="number"
                                    style={styles.input}
                                    value={discount}
                                    min="0"
                                    onChange={(e) =>
                                        setDiscount(parseFloat(e.target.value) || 0)
                                    }
                                    placeholder="0.00"
                                />
                            </div>
                            <div>
                                <label style={styles.label}>Shipping</label>
                                <input
                                    type="number"
                                    style={styles.input}
                                    value={shipping}
                                    min="0"
                                    onChange={(e) =>
                                        setShipping(parseFloat(e.target.value) || 0)
                                    }
                                    placeholder="0.00"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {/* Summary */}
                <div style={styles.section}>
                    <h6 style={styles.sectionHeader}>Order Summary</h6>
                    <div style={styles.sectionBody}>
                        <div style={{ marginTop: "8px" }}>
                            <div style={styles.summaryRow}>
                                <span style={styles.summaryLabel}>Subtotal</span>
                                <span style={styles.summaryValue}>{formatKES(totals.subTotal)}</span>
                            </div>
                            <div style={styles.summaryRow}>
                                <span style={styles.summaryLabel}>Tax</span>
                                <span style={styles.summaryValue}>{formatKES(totals.tax)}</span>
                            </div>
                            <div style={styles.summaryRow}>
                                <span style={styles.summaryLabel}>Discount</span>
                                <span style={{ ...styles.summaryValue, color: "#dc2626" }}>
                                    -{formatKES(totals.discount)}
                                </span>
                            </div>
                            <div style={styles.summaryRow}>
                                <span style={styles.summaryLabel}>Shipping</span>
                                <span style={styles.summaryValue}>{formatKES(totals.shipping)}</span>
                            </div>
                            <div style={styles.grandTotalRow}>
                                <span>Grand Total</span>
                                <span>{formatKES(totals.grandTotal)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Submit */}
            <div style={{ marginTop: "8px", marginBottom: "24px" }}>
                <button
                    type="button"
                    style={styles.submitBtn}
                    onClick={handleSubmit}
                    onMouseEnter={(e) => (e.currentTarget.style.transform = "scale(1.02)")}
                    onMouseLeave={(e) => (e.currentTarget.style.transform = "scale(1)")}
                >
                    <i className="fas fa-save"></i>
                    {purchaseId ? "Update Purchase" : "Create Purchase"}
                </button>
            </div>

            <Toaster position="top-right" reverseOrder={false} />
        </>
    );
}
