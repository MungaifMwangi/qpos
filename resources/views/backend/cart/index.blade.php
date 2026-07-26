@extends('backend.master')
@section('title', ' ')
@section('content')
<div id="cart"></div>
@push('style')
<style>
    .products-card-container {
        height: calc(100vh - 180px);
        min-height: 500px;
        overflow-y: auto;
        overflow-x: hidden;
        border: 1px solid #e8e8e8;
        border-radius: 14px;
        padding: 12px;
        background: #fff;
    }

    .products-card-container::-webkit-scrollbar {
        width: 6px;
    }
    .products-card-container::-webkit-scrollbar-track {
        background: #f5f5f5;
        border-radius: 3px;
    }
    .products-card-container::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 3px;
    }
    .products-card-container::-webkit-scrollbar-thumb:hover {
        background: #aaa;
    }

    .product-card-item {
        border: 1px solid #e8e8e8;
        border-radius: 12px;
        padding: 10px;
        transition: all 0.2s;
        background: #fff;
        cursor: pointer;
    }
    .product-card-item:hover {
        border-color: #d35400;
        box-shadow: 0 2px 8px rgba(211,84,0,0.12);
        transform: translateY(-1px);
    }

    .product-name {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 14px;
        overflow: hidden;
        white-space: normal;
        text-overflow: ellipsis;
        color: #303030;
    }

    .product-details p {
        margin: 0;
        font-size: 14px;
        color: #999;
    }

    .loading-more {
        text-align: center;
        padding: 16px;
        font-weight: 600;
        color: #999;
        font-size: 13px;
    }

    .responsive-table {
        max-height: 320px;
        overflow-y: auto;
    }

    .qty {
        -moz-appearance: textfield;
        -webkit-appearance: none;
        appearance: none;
        text-align: center;
        font-weight: 600;
    }

    .qty::-webkit-inner-spin-button,
    .qty::-webkit-outer-spin-button {
        display: none;
    }

    .cart-table th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #999;
        padding: 8px 10px;
        border-bottom: 1px solid #e8e8e8;
    }

    .cart-table td {
        padding: 8px 10px;
        font-size: 13px;
        vertical-align: middle;
    }

    .pos-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        font-size: 13px;
    }

    .pos-summary-label {
        color: #666;
        font-weight: 500;
    }

    .pos-summary-value {
        font-weight: 700;
        color: #303030;
    }

    .pos-grand-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0 0;
        margin-top: 6px;
        border-top: 2px solid #e8e8e8;
        font-size: 18px;
        font-weight: 800;
        color: #d35400;
    }

    .pos-btn-clear {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        padding: 10px;
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }
    .pos-btn-clear:hover { background: #fecaca; }

    .pos-btn-checkout {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        padding: 10px;
        background: linear-gradient(135deg,#1a7a4e,#28a745);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }
    .pos-btn-checkout:hover { opacity: 0.9; transform: scale(1.01); }

    .pos-search-input {
        border: 1px solid #e8e8e8;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 14px;
        width: 100%;
        outline: none;
        transition: border-color 0.2s;
    }
    .pos-search-input:focus { border-color: #d35400; }

    .pos-cart-section {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 12px;
    }

    .pos-cart-header {
        padding: 12px 16px;
        font-weight: 700;
        font-size: 13px;
        color: #303030;
        border-bottom: 1px solid #f0f0f0;
    }

    .pos-inc-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 6px;
        border: 1px solid #d4edda;
        background: #d4edda;
        color: #155724;
        font-size: 11px;
        cursor: pointer;
    }
    .pos-dec-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 6px;
        border: 1px solid #fff3cd;
        background: #fff3cd;
        color: #856404;
        font-size: 11px;
        cursor: pointer;
    }
    .pos-del-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 6px;
        border: 1px solid #fee2e2;
        background: #fee2e2;
        color: #dc2626;
        font-size: 11px;
        cursor: pointer;
    }
</style>
@endpush
@endsection