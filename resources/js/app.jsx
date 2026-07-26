// import './bootstrap';
import React from 'react'
import Pos from "./components/Pos";
import Purchase from './components/Purchase/Purchase';
import ProfitLossReport from './components/ProfitLossReport';
import { createRoot } from 'react-dom/client';

// Check for the 'cart' element and render the 'cart' component using createRoot
if (document.getElementById("cart")) {
    const cartRoot = createRoot(document.getElementById("cart"));
    cartRoot.render(<Pos />);
}

// Check for the 'purchase' element and render the 'Purchase' component using createRoot
if (document.getElementById("purchase")) {
    const purchaseRoot = createRoot(
        document.getElementById("purchase")
    );
    purchaseRoot.render(<Purchase />);
}

// Check for the 'profit-loss-report' element and render the ProfitLossReport component
if (document.getElementById("profit-loss-report")) {
    const plRoot = createRoot(document.getElementById("profit-loss-report"));
    plRoot.render(<ProfitLossReport />);
}

