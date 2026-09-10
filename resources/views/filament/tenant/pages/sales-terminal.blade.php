@php
    $currency = $this->currency();
    $products = $this->products();
    $categories = $this->categories();
    $customers = $this->customers();
    $paymentMethods = $this->paymentMethods();
    $cartCount = collect($cart)->sum('quantity');
    $user = auth()->user();
    $userName = $user?->full_name ?? $user?->name ?? 'Admin User';
    $avatarUrl = $user?->getFilamentAvatarUrl();
    $dash = $this->dashboardAccess();
    $initials = collect(explode(' ', trim($userName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->join('') ?: 'AU';
@endphp

<x-filament-panels::page>
    <style>
        body.dreams-pos-body {
            overflow: hidden;
        }

        body.dreams-pos-body .fi-main-sidebar,
        body.dreams-pos-body .fi-topbar-open-sidebar-btn,
        body.dreams-pos-body .fi-topbar-close-sidebar-btn {
            display: none !important;
        }

        body.dreams-pos-body .fi-topbar-ctn,
        body.dreams-pos-body .fi-topbar {
            display: none !important;
        }

        body.dreams-pos-body .fi-main-ctn {
            width: 100% !important;
            max-width: none !important;
        }

        body.dreams-pos-body .fi-main {
            width: 100% !important;
            max-width: none !important;
            height: 100dvh !important;
            padding: 0 !important;
            overflow: hidden;
        }

        body.dreams-pos-body .fi-main > .fi-page,
        body.dreams-pos-body .fi-main > .fi-page-content {
            height: 100% !important;
        }

        body.dreams-pos-body .fi-page {
            display: flex;
            flex-direction: column;
        }

        body.dreams-pos-body .fi-page-header-main-ctn {
            height: 100dvh !important;
            overflow: hidden;
            padding: 0 !important;
            flex: 1 1 auto;
            min-height: 0;
        }

        body.dreams-pos-body .fi-page-main {
            flex: 1 1 auto;
            min-height: 0;
            height: 100% !important;
            display: flex;
            flex-direction: column;
        }

        body.dreams-pos-body .fi-page-content {
            display: block !important;
            gap: 0 !important;
            overflow: hidden;
            flex: 1 1 auto;
            min-height: 0;
            height: 100% !important;
        }
        .scanner-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
        }

        .dreams-pos,
        .dreams-pos * {
            box-sizing: border-box;
        }

        .dreams-pos {
            --pos-navy: #102a43;
            --pos-orange: #ff9638;
            --pos-orange-dark: #f18422;
            --pos-border: #e3e8ed;
            --pos-muted: #6b7280;
            --pos-bg: #f5f7f9;
            height: 100%;
            display: flex;
            flex-direction: column;
            color: #243447;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .dreams-pos-navbar {
            flex: 0 0 65px;
            height: 65px;
            display: flex;
            align-items: center;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            color: #102a43;
            z-index: 40;
        }

        .dreams-pos-navbar * {
            box-sizing: border-box;
        }

        .dreams-pos-navbrand {
            height: 100%;
            display: flex;
            align-items: center;
            flex: 0 0 auto;
            padding: 0 22px;
        }

        .dreams-pos-navbrand-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--pos-navy);
            text-decoration: none;
        }

        .dreams-pos-navlogo {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            color: var(--pos-navy);
            background: linear-gradient(135deg, #102a43, #173f66);
            border-radius: 10px;
        }

        .dreams-pos-navlogo svg {
            color: var(--pos-orange);
        }

        .dreams-pos-navname {
            position: relative;
            display: flex;
            flex-direction: column;
            line-height: 1;
        }

        .dreams-pos-navlabel {
            position: absolute;
            top: -9px;
            left: 2px;
            color: var(--pos-orange);
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .13em;
        }

        .dreams-pos-navtitle {
            margin-top: 8px;
            font-size: 23px;
            font-weight: 850;
            letter-spacing: -.04em;
            color: var(--pos-navy);
        }

        .dreams-pos-navmain {
            min-width: 0;
            flex: 1 1 auto;
            height: 100%;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 18px;
        }

        .dreams-pos-navspacer {
            flex: 1 1 auto;
            min-width: 12px;
        }

        .dreams-pos-navchip {
            height: 32px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 0 12px;
            border-radius: 7px;
            background: #0f766e;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .dreams-pos-navbtn {
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 0 13px;
            border: 1px solid transparent;
            border-radius: 7px;
            background: #f3f4f6;
            color: #1f2937;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: background .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
        }

        .dreams-pos-navbtn:hover {
            background: #e9edf1;
            transform: translateY(-1px);
        }

        .dreams-pos-navbtn.dashboard {
            background: #7c3aed;
            color: #ffffff;
            border-color: #7c3aed;
        }

        .dreams-pos-navbtn.dashboard:hover {
            background: #6d28d9;
        }

        .dreams-pos-navbtn.calc {
            width: 34px;
            padding: 0;
            background: var(--pos-orange);
            color: #ffffff;
            border-color: var(--pos-orange);
        }

        .dreams-pos-navbtn.calc:hover {
            background: var(--pos-orange-dark);
        }

        .dreams-pos-navbtn.icon {
            width: 34px;
            padding: 0;
        }

        .dreams-store-wrap {
            position: relative;
            flex: 0 0 auto;
        }

        .dreams-pos-store-btn {
            height: 34px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0 11px;
            border: 1px solid #e3e8ed;
            border-radius: 7px;
            background: #ffffff;
            color: #243447;
            font-size: 12.5px;
            font-weight: 650;
            cursor: pointer;
            transition: border-color .18s ease, background .18s ease;
        }

        .dreams-pos-store-btn:hover {
            background: #f8fafc;
        }

        .dreams-pos-store-icon {
            width: 18px;
            height: 18px;
            display: grid;
            place-items: center;
            border-radius: 5px;
            background: #052e16;
            color: #4ade80;
        }

        .dreams-pos-navprofile {
            position: relative;
            flex: 0 0 auto;
            margin-left: 2px;
            padding-right: 6px;
        }

        .dreams-pos-navavatar {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 8px;
            background: #eef1f4;
            color: #163957;
            cursor: pointer;
            overflow: hidden;
            transition: background .18s ease, transform .18s ease;
        }

        .dreams-pos-navavatar:hover {
            background: #e3e8ee;
            transform: translateY(-1px);
        }

        .dreams-pos-navavatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .dreams-pos-navavatar .dreams-avatar-fallback {
            width: 100%;
            height: 100%;
            display: grid;
            place-items: center;
            background: var(--pos-orange);
            color: #ffffff;
            font-size: 12px;
            font-weight: 850;
        }

        .dreams-pos-navmenu {
            position: absolute;
            top: calc(100% + 10px);
            right: 6px;
            min-width: 168px;
            overflow: hidden;
            border: 1px solid rgba(227, 232, 237, .95);
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .14);
            z-index: 90;
        }

        .dreams-pos-navmenu-item {
            display: block;
            width: 100%;
            padding: 10px 14px;
            border: 0;
            background: #ffffff;
            color: #243447;
            font-size: 13px;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
        }

        .dreams-pos-navmenu-item:hover {
            background: #f8fafc;
        }

        [x-cloak] {
            display: none !important;
        }

        .dreams-pos-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 430px;
            grid-template-rows: minmax(0, 1fr);
            gap: 16px;
            flex: 1 1 auto;
            min-height: 0;
            padding: 16px;
        }

        .dreams-pos-left,
        .dreams-pos-cart {
            border: 1px solid var(--pos-border);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .045);
        }

        .dreams-pos-left {
            min-width: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        .dreams-pos-cart {
            height: 100%;
            min-height: 0;
            overflow-y: auto;
        }

        .dreams-pos-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 16px;
            border-bottom: 1px solid var(--pos-border);
            background: #ffffff;
        }

        .dreams-pos-title h1 {
            margin: 0;
            color: var(--pos-navy);
            font-size: 22px;
            font-weight: 850;
            letter-spacing: -.03em;
            line-height: 1.1;
        }

        .dreams-pos-title p {
            margin: 5px 0 0;
            color: var(--pos-muted);
            font-size: 12px;
        }

        .dreams-pos-actions {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .dreams-pos-chip,
        .dreams-pos-icon-btn {
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border: 1px solid var(--pos-border);
            border-radius: 9px;
            background: #ffffff;
            color: #334155;
            font-size: 12px;
            font-weight: 760;
            text-decoration: none;
        }

        .dreams-pos-chip {
            padding: 0 12px;
        }

        .dreams-pos-icon-btn {
            width: 36px;
        }

        .dreams-pos-icon-btn.has-label {
            width: auto;
            padding: 0 12px;
            white-space: nowrap;
        }

        .dreams-pos-filterbar {
            display: grid;
            grid-template-columns: minmax(220px, 320px) minmax(0, 1fr);
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--pos-border);
            background: #fbfcfd;
        }

        .dreams-pos-search {
            height: 42px;
            display: flex;
            align-items: center;
            gap: 9px;
            border: 1px solid var(--pos-border);
            border-radius: 10px;
            background: #ffffff;
            color: #64748b;
            padding: 0 12px;
            transition: border-color .18s ease, box-shadow .18s ease;
        }

        .dreams-pos-search:focus-within {
            border-color: var(--pos-orange);
            box-shadow: 0 0 0 3px rgba(255, 150, 56, .12);
        }

        .dreams-pos-search input {
            min-width: 0;
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: #243447;
            font-size: 13px;
        }

        .dreams-categories {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            overflow-x: auto;
            padding-bottom: 2px;
        }

        .dreams-category-btn {
            height: 42px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex: 0 0 auto;
            border: 1px solid var(--pos-border);
            border-radius: 10px;
            background: #ffffff;
            color: #334155;
            cursor: pointer;
            font-size: 12px;
            font-weight: 780;
            padding: 0 12px;
            transition: background .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease;
        }

        .dreams-category-btn:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .dreams-category-btn.is-active {
            border-color: var(--pos-orange);
            background: var(--pos-orange);
            color: #ffffff;
            box-shadow: 0 8px 18px rgba(255, 150, 56, .18);
        }

        .dreams-products-wrap {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: 16px;
            background: #f7f9fb;
        }

        .dreams-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(156px, 1fr));
            gap: 14px;
        }

        .dreams-product-card {
            overflow: hidden;
            border: 1px solid var(--pos-border);
            border-radius: 13px;
            background: #ffffff;
            cursor: pointer;
            text-align: left;
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .dreams-product-card:hover {
            border-color: rgba(255, 150, 56, .7);
            box-shadow: 0 14px 28px rgba(15, 23, 42, .09);
            transform: translateY(-2px);
        }

        .dreams-product-image {
            position: relative;
            height: 122px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(135deg, #eef3f7, #f8fafc);
            color: #163957;
        }

        .dreams-product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .dreams-product-badge {
            position: absolute;
            top: 9px;
            left: 9px;
            max-width: calc(100% - 18px);
            overflow: hidden;
            border-radius: 999px;
            background: rgba(16, 42, 67, .86);
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            line-height: 20px;
            padding: 0 8px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dreams-product-body {
            padding: 12px;
        }

        .dreams-product-name {
            margin: 0;
            overflow: hidden;
            color: var(--pos-navy);
            font-size: 13px;
            font-weight: 820;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dreams-product-code {
            margin-top: 5px;
            overflow: hidden;
            color: var(--pos-muted);
            font-size: 11px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dreams-product-foot {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 10px;
            margin-top: 12px;
        }

        .dreams-product-price {
            color: var(--pos-orange-dark);
            font-size: 14px;
            font-weight: 850;
        }

        .dreams-product-stock {
            color: #64748b;
            font-size: 11px;
            font-weight: 720;
            text-align: right;
        }

        .dreams-cart-head {
            flex: 0 0 auto;
            padding: 12px 16px;
            border-bottom: 1px solid var(--pos-border);
            background: #ffffff;
        }

        .dreams-cart-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .dreams-cart-title {
            margin: 0;
            color: var(--pos-navy);
            font-size: 18px;
            font-weight: 850;
            letter-spacing: -.02em;
        }

        .dreams-cart-count {
            height: 24px;
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: #fff0e2;
            color: var(--pos-orange-dark);
            font-size: 12px;
            font-weight: 820;
            padding: 0 10px;
        }

        .dreams-cart-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 10px;
        }

        .dreams-select,
        .dreams-number-input {
            width: 100%;
            height: 34px;
            border: 1px solid var(--pos-border);
            border-radius: 9px;
            background: #ffffff;
            color: #243447;
            font-size: 12px;
            outline: 0;
            padding: 0 10px;
        }

        .dreams-cart-items {
            padding: 12px 16px;
            background: #fbfcfd;
        }

        .dreams-cart-item {
            display: grid;
            grid-template-columns: 44px minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--pos-border);
            border-radius: 11px;
            background: #ffffff;
        }

        .dreams-cart-item + .dreams-cart-item {
            margin-top: 9px;
        }

        .dreams-cart-thumb {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border-radius: 10px;
            background: #eef3f7;
            color: #163957;
        }

        .dreams-cart-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .dreams-cart-name {
            margin: 0;
            overflow: hidden;
            color: var(--pos-navy);
            font-size: 13px;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dreams-cart-meta {
            margin-top: 4px;
            color: #64748b;
            font-size: 11px;
        }

        .dreams-qty {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
        }

        .dreams-qty button,
        .dreams-remove {
            border: 0;
            cursor: pointer;
            transition: background .18s ease, color .18s ease;
        }

        .dreams-qty button {
            width: 24px;
            height: 24px;
            display: grid;
            place-items: center;
            border-radius: 7px;
            background: #f1f5f9;
            color: var(--pos-navy);
            font-weight: 800;
        }

        .dreams-qty button:hover {
            background: #e2e8f0;
        }

        .dreams-qty span {
            min-width: 22px;
            color: var(--pos-navy);
            font-size: 12px;
            font-weight: 820;
            text-align: center;
        }

        .dreams-line-total {
            color: var(--pos-navy);
            font-size: 13px;
            font-weight: 850;
            text-align: right;
            white-space: nowrap;
        }

        .dreams-remove {
            display: inline-grid;
            width: 28px;
            height: 28px;
            place-items: center;
            margin-top: 9px;
            margin-left: auto;
            border-radius: 8px;
            background: #fee2e2;
            color: #dc2626;
        }

        .dreams-cart-empty {
            height: 240px;
            display: grid;
            place-items: center;
            border: 1px dashed #cbd5e1;
            border-radius: 13px;
            background: #ffffff;
            color: var(--pos-muted);
            text-align: center;
            padding: 18px;
        }

        .dreams-cart-empty strong {
            display: block;
            margin-top: 10px;
            color: var(--pos-navy);
            font-size: 14px;
        }

        .dreams-summary {
            flex: 0 0 auto;
            padding: 10px 14px 12px;
            border-top: 1px solid var(--pos-border);
            background: #ffffff;
        }

        .dreams-summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: #475569;
            font-size: 12px;
            padding: 3px 0;
        }

        .dreams-summary-row strong {
            color: var(--pos-navy);
            font-weight: 820;
        }

        .dreams-discount-row {
            display: grid;
            grid-template-columns: 1fr 118px;
            gap: 10px;
            align-items: center;
            padding: 4px 0;
        }

        .dreams-total-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 6px;
            padding: 10px 12px;
            border-radius: 12px;
            background: var(--pos-navy);
            color: #ffffff;
        }

        .dreams-total-row span {
            font-size: 12px;
            font-weight: 760;
            opacity: .84;
        }

        .dreams-total-row strong {
            font-size: 20px;
            font-weight: 880;
        }

        .dreams-payment-methods {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px;
            margin-top: 8px;
        }

        .dreams-pay-radio {
            position: relative;
        }

        .dreams-pay-radio input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .dreams-pay-radio span {
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--pos-border);
            border-radius: 9px;
            background: #ffffff;
            color: #334155;
            cursor: pointer;
            font-size: 11px;
            font-weight: 780;
            transition: background .18s ease, border-color .18s ease, color .18s ease;
        }

        .dreams-pay-radio input:checked + span {
            border-color: var(--pos-orange);
            background: #fff0e2;
            color: var(--pos-orange-dark);
        }

        .dreams-checkout-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 8px;
        }

        .dreams-action-btn {
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 820;
            transition: background .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .dreams-action-btn:hover:not(:disabled) {
            transform: translateY(-1px);
        }

        .dreams-action-btn:disabled {
            cursor: not-allowed;
            opacity: .55;
        }

        .dreams-action-btn.clear {
            background: #fee2e2;
            color: #b91c1c;
        }

        .dreams-action-btn.pay {
            grid-column: span 2;
            background: var(--pos-orange);
            color: #ffffff;
            box-shadow: 0 10px 22px rgba(255, 150, 56, .24);
        }

        .dreams-action-btn.pay:hover:not(:disabled) {
            background: var(--pos-orange-dark);
        }

        .dreams-action-btn.hold {
            background: #0f766e;
            color: #ffffff;
        }

        .dreams-action-btn.hold:hover:not(:disabled) {
            background: #115e59;
        }

        .dreams-action-btn.void {
            background: #ef4444;
            color: #ffffff;
        }

        .dreams-action-btn.void:hover:not(:disabled) {
            background: #dc2626;
        }

        .dreams-cart-headbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 10px;
        }

        .dreams-cart-headbar .dreams-pos-icon-btn {
            height: 34px;
            width: 34px;
            flex: 0 0 auto;
        }

        .dreams-pos-icon-btn.is-active {
            border-color: var(--pos-orange);
            color: var(--pos-orange-dark);
        }

        .dreams-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 120;
            display: grid;
            place-items: center;
            padding: 24px;
            background: rgba(15, 23, 42, .5);
            backdrop-filter: blur(3px);
        }

        .dreams-modal {
            width: 100%;
            max-width: 560px;
            max-height: calc(100dvh - 60px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(227, 232, 237, .9);
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 30px 70px rgba(15, 23, 42, .28);
        }

        .dreams-modal.wide {
            max-width: 820px;
        }

        .dreams-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--pos-border);
            background: #ffffff;
        }

        .dreams-modal-head h3 {
            margin: 0;
            color: var(--pos-navy);
            font-size: 17px;
            font-weight: 850;
            letter-spacing: -.02em;
        }

        .dreams-modal-close {
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border: 1px solid var(--pos-border);
            border-radius: 9px;
            background: #ffffff;
            color: #64748b;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
        }

        .dreams-modal-close:hover {
            background: #f1f5f9;
            color: #b91c1c;
        }

        .dreams-modal-body {
            overflow-y: auto;
            padding: 18px 20px;
        }

        .dreams-modal-tabs {
            display: flex;
            gap: 6px;
            padding: 12px 20px 0;
            background: #ffffff;
        }

        .dreams-modal-tab {
            height: 34px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0 13px;
            border: 1px solid var(--pos-border);
            border-radius: 9px 9px 0 0;
            background: #f1f5f9;
            color: #475569;
            cursor: pointer;
            font-size: 12px;
            font-weight: 760;
        }

        .dreams-modal-tab.is-active {
            background: #ffffff;
            color: var(--pos-orange-dark);
            border-color: var(--pos-border);
            border-bottom-color: #ffffff;
            box-shadow: inset 0 2px 0 var(--pos-orange);
        }

        .dreams-order-table {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .dreams-order-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto auto;
            gap: 12px;
            align-items: center;
            padding: 12px 14px;
            border: 1px solid var(--pos-border);
            border-radius: 12px;
            background: #ffffff;
        }

        .dreams-order-main {
            min-width: 0;
        }

        .dreams-order-no {
            margin: 0;
            overflow: hidden;
            color: var(--pos-navy);
            font-size: 13px;
            font-weight: 820;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dreams-order-meta {
            margin-top: 3px;
            color: #64748b;
            font-size: 11px;
        }

        .dreams-order-total {
            color: var(--pos-navy);
            font-size: 13px;
            font-weight: 850;
            text-align: right;
            white-space: nowrap;
        }

        .dreams-order-status {
            display: inline-flex;
            align-items: center;
            height: 22px;
            padding: 0 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
        }

        .dreams-order-status.paid {
            background: #dcfce7;
            color: #15803d;
        }

        .dreams-order-status.unpaid {
            background: #fee2e2;
            color: #b91c1c;
        }

        .dreams-order-status.held {
            background: #ffedd5;
            color: #c2410c;
        }

        .dreams-order-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .dreams-order-btn {
            height: 30px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 0 10px;
            border: 1px solid var(--pos-border);
            border-radius: 8px;
            background: #ffffff;
            color: #334155;
            cursor: pointer;
            font-size: 11px;
            font-weight: 780;
            text-decoration: none;
        }

        .dreams-order-btn:hover {
            background: #f8fafc;
        }

        .dreams-order-btn.resume {
            background: #0f766e;
            border-color: #0f766e;
            color: #ffffff;
        }

        .dreams-order-btn.danger {
            background: #fee2e2;
            border-color: #fecaca;
            color: #b91c1c;
        }

        .dreams-collect-input {
            width: 120px;
            height: 30px;
            border: 1px solid var(--pos-border);
            border-radius: 8px;
            background: #ffffff;
            color: #243447;
            font-size: 12px;
            outline: 0;
            padding: 0 8px;
        }

        .dreams-collect-input:focus {
            border-color: var(--pos-orange);
            box-shadow: 0 0 0 3px rgba(255, 150, 56, .12);
        }

        .dreams-pay-type-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .dreams-pay-type-btn {
            flex: 1;
            min-width: 90px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border: 1px solid var(--pos-border);
            border-radius: 9px;
            background: #ffffff;
            color: #334155;
            font-size: 12px;
            font-weight: 720;
            cursor: pointer;
            padding: 0 12px;
        }

        .dreams-pay-type-btn.is-active {
            border-color: var(--pos-orange);
            background: var(--pos-orange);
            color: #ffffff;
            box-shadow: 0 6px 14px rgba(255, 150, 56, .22);
        }

        .dreams-credit-note {
            padding: 10px 12px;
            border-radius: 9px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            font-size: 12px;
            line-height: 1.55;
            margin-bottom: 14px;
        }

        .dreams-credit-note strong {
            color: #78350f;
        }

        .dreams-modal-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 12px;
        }

        .dreams-modal-field label {
            color: var(--pos-navy);
            font-size: 11.5px;
            font-weight: 760;
        }

        .dreams-modal-field input,
        .dreams-modal-field select {
            width: 100%;
            height: 38px;
            border: 1px solid var(--pos-border);
            border-radius: 9px;
            background: #ffffff;
            color: #243447;
            font-size: 13px;
            outline: 0;
            padding: 0 12px;
        }

        .dreams-modal-field input:focus,
        .dreams-modal-field select:focus {
            border-color: var(--pos-orange);
            box-shadow: 0 0 0 3px rgba(255, 150, 56, .12);
        }

        .dreams-modal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .dreams-pay-summary {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin: 2px 0 14px;
            padding: 14px;
            border: 1px solid var(--pos-border);
            border-radius: 12px;
            background: #fbfcfd;
        }

        .dreams-pay-summary .dreams-summary-row {
            padding: 0;
        }

        .dreams-pay-total {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 11px;
            background: var(--pos-navy);
            color: #ffffff;
            font-size: 13px;
            font-weight: 780;
        }

        .dreams-pay-total strong {
            font-size: 20px;
            font-weight: 880;
        }

        .dreams-pay-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 16px;
        }

        .dreams-pay-actions .dreams-action-btn {
            min-width: 140px;
        }

        .dreams-add-customer-btn {
            width: 34px;
            height: 34px;
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed #94a3b8;
            border-radius: 9px;
            background: #ffffff;
            color: #64748b;
            cursor: pointer;
            transition: border-color .18s ease, color .18s ease, background .18s ease;
        }

        .dreams-add-customer-btn:hover {
            border-color: var(--pos-orange);
            color: var(--pos-orange-dark);
            background: #fff7ed;
        }

        .dreams-split-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 14px 0 2px;
            padding: 10px 14px;
            border: 1px solid var(--pos-border);
            border-radius: 11px;
            background: #ffffff;
            cursor: pointer;
        }

        .dreams-split-toggle .dreams-split-label {
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--pos-navy);
            font-size: 12.5px;
            font-weight: 800;
        }

        .dreams-switch {
            position: relative;
            width: 40px;
            height: 22px;
            flex: 0 0 auto;
            border-radius: 999px;
            background: #cbd5e1;
            transition: background .18s ease;
        }

        .dreams-switch::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .3);
            transition: transform .18s ease;
        }

        .dreams-switch.is-on {
            background: var(--pos-orange);
        }

        .dreams-switch.is-on::after {
            transform: translateX(18px);
        }

        .dreams-split-rows {
            display: flex;
            flex-direction: column;
            gap: 9px;
            margin-top: 12px;
        }

        .dreams-split-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 118px 30px;
            gap: 9px;
            align-items: center;
        }

        .dreams-split-row select,
        .dreams-split-row input {
            width: 100%;
            height: 36px;
            border: 1px solid var(--pos-border);
            border-radius: 9px;
            background: #ffffff;
            color: #243447;
            font-size: 12.5px;
            outline: 0;
            padding: 0 10px;
        }

        .dreams-split-remove {
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border: 1px solid #fecaca;
            border-radius: 8px;
            background: #fee2e2;
            color: #b91c1c;
            cursor: pointer;
        }

        .dreams-split-add {
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin-top: 4px;
            padding: 0 13px;
            border: 1px dashed #94a3b8;
            border-radius: 9px;
            background: #ffffff;
            color: #475569;
            cursor: pointer;
            font-size: 12px;
            font-weight: 780;
        }

        .dreams-split-add:hover {
            border-color: var(--pos-orange);
            color: var(--pos-orange-dark);
            background: #fff7ed;
        }

        .dreams-split-total {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 12px;
            padding: 10px 14px;
            border-radius: 11px;
            background: #f1f5f9;
            color: var(--pos-navy);
            font-size: 12.5px;
            font-weight: 820;
        }

        .dreams-split-total strong {
            font-size: 16px;
            font-weight: 880;
        }

        .dreams-receipt-modal {
            max-width: 430px;
        }

        .dreams-receipt {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            padding: 18px 16px;
            background: #ffffff;
            color: #1e293b;
            font-family: 'Courier New', Courier, monospace;
        }

        .dreams-receipt-center {
            text-align: center;
        }

        .dreams-receipt h2 {
            margin: 0;
            color: var(--pos-navy);
            font-size: 17px;
            font-weight: 850;
            letter-spacing: .03em;
        }

        .dreams-receipt p {
            margin: 2px 0;
            font-size: 11px;
            line-height: 1.5;
            color: #334155;
        }

        .dreams-receipt hr {
            margin: 8px 0;
            border: 0;
            border-top: 1px dashed #cbd5e1;
        }

        .dreams-receipt-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .dreams-receipt-table th {
            padding: 3px 0;
            text-align: left;
            border-bottom: 1px dashed #cbd5e1;
            color: #0f172a;
            font-weight: 800;
        }

        .dreams-receipt-table th:nth-child(3),
        .dreams-receipt-table td:nth-child(3) {
            text-align: right;
        }

        .dreams-receipt-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .dreams-receipt-item-name {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dreams-receipt-qty {
            text-align: center;
            white-space: nowrap;
        }

        .dreams-receipt-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 2px 0;
            font-size: 11.5px;
        }

        .dreams-receipt-line strong {
            color: #0f172a;
        }

        .dreams-receipt-grand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 6px 0;
            border-top: 1px solid #0f172a;
            border-bottom: 1px solid #0f172a;
            font-size: 13px;
            font-weight: 850;
            color: #0f172a;
        }

        .dreams-receipt-footer {
            margin-top: 8px;
            font-size: 10.5px;
            line-height: 1.6;
            color: #475569;
        }

        .dreams-receipt-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            padding: 14px 20px;
            border-top: 1px solid var(--pos-border);
            background: #ffffff;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .dreams-receipt-printable,
            .dreams-receipt-printable * {
                visibility: visible;
            }

            .dreams-receipt-printable {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
            }

            .dreams-receipt-actions,
            .dreams-modal-close,
            .dreams-modal-head {
                display: none !important;
            }

            .dreams-modal-overlay {
                position: absolute;
                padding: 0;
                background: #ffffff;
                backdrop-filter: none;
            }

            .dreams-receipt-modal {
                max-width: 100%;
                box-shadow: none;
                border: 0;
            }
        }

        @media (max-width: 1180px) {
            .dreams-pos-navbrand {
                width: 210px;
                flex-basis: 210px;
            }

            .dreams-pos-shell {
                grid-template-columns: 1fr;
                height: auto;
                min-height: 0;
                overflow-y: auto;
            }

            .dreams-pos-left,
            .dreams-pos-cart {
                height: auto;
                max-height: none;
            }

            .dreams-pos-cart {
                overflow-y: auto;
            }
        }

        @media (max-width: 920px) {
            .dreams-pos-navbrand {
                padding-left: 16px;
            }

            .dreams-pos-navtitle {
                font-size: 20px;
            }

            .dreams-pos-navlogo {
                width: 32px;
                height: 32px;
            }

            .dreams-pos-navmain {
                gap: 7px;
                padding: 0 10px;
            }

            .dreams-pos-navchip {
                display: none;
            }
        }

        @media (max-width: 760px) {
            .dreams-pos-navbar {
                height: auto;
                min-height: 65px;
                flex-wrap: wrap;
            }

            .dreams-pos-navbrand {
                height: 65px;
                width: 100%;
                flex: 0 0 100%;
                border-bottom: 1px solid #e5e7eb;
            }

            .dreams-pos-navmain {
                height: 54px;
                width: 100%;
                flex: 0 0 100%;
                padding: 0 12px;
                overflow-x: auto;
            }

            .dreams-pos-navbtn,
            .dreams-pos-store-btn {
                flex: 0 0 auto;
                white-space: nowrap;
            }

            .dreams-pos-navspacer {
                display: none;
            }

            .dreams-pos-toolbar,
            .dreams-pos-filterbar,
            .dreams-cart-form {
                grid-template-columns: 1fr;
                align-items: stretch;
                flex-direction: column;
            }

            .dreams-products-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            }
        }
    </style>

    <div class="dreams-pos" wire:poll.1s="pollScanQueue">
        <nav class="dreams-pos-navbar" x-data="{ profileOpen: false, storeOpen: false, dashboardOpen: false, scannerOpen: false }">
            <div class="dreams-pos-navbrand">
                <a href="{{ $dash['canAdmin'] ? route('filament.tenant.pages.admin-dashboard') : ($dash['allowed']['url'] ?? '#') }}" class="dreams-pos-navbrand-link" aria-label="Dashboard">
                    <span class="dreams-pos-navlogo" aria-hidden="true">
                        <svg viewBox="0 0 44 44" width="34" height="34" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 17v-3.5C14 8.3 17.6 5 22 5s8 3.3 8 8.5V17" />
                            <path d="M10 17h24l2.2 19.2A3.5 3.5 0 0 1 32.8 40H11.2a3.5 3.5 0 0 1-3.4-3.8L10 17Z" />
                            <path d="M17 24h10" />
                            <path d="M17 30h7" />
                        </svg>
                    </span>
                    <span class="dreams-pos-navname">
                        <span class="dreams-pos-navlabel">@lang('pos.pos')</span>
                        <span class="dreams-pos-navtitle">HALIS</span>
                    </span>
                </a>
            </div>

            <div class="dreams-pos-navmain">
                <span class="dreams-pos-navchip">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    <span x-data="{ now: new Date().toLocaleTimeString('en-GB') }" x-init="setInterval(() => now = new Date().toLocaleTimeString('en-GB'), 1000)" x-text="now"></span>
                </span>

                <div class="dreams-pos-navspacer"></div>

                @if ($dash['canAdmin'])
                    <a href="{{ route('filament.tenant.pages.admin-dashboard') }}" class="dreams-pos-navbtn dashboard">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
                        @lang('pos.dashboard')
                    </a>
                @else
                    <button type="button" class="dreams-pos-navbtn dashboard" x-on:click="dashboardOpen = true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
                        @lang('pos.dashboard')
                    </button>
                @endif

                @php
                    $branchService = app(\App\Services\BranchService::class);
                    $availableBranches = $branchService->getUserBranches();
                    $activeBranch = $branchService->getActiveBranch();
                @endphp
                @if(isset($availableBranches) && count($availableBranches) > 0)
                <div class="dreams-store-wrap" x-on:click.outside="storeOpen = false">
                    @if(count($availableBranches) > 1)
                    <button type="button" class="dreams-pos-store-btn" x-on:click="storeOpen = ! storeOpen" aria-haspopup="menu" x-bind:aria-expanded="storeOpen">
                        <span class="dreams-pos-store-icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10h18l-1.6-5.2A2.4 2.4 0 0 0 17.1 3H6.9a2.4 2.4 0 0 0-2.3 1.8L3 10Z"/><path d="M5 10v9h14v-9"/><path d="M9 19v-5h6v5"/></svg>
                        </span>
                        <span>{{ $activeBranch?->name ?? __('pos.select_branch') }}</span>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="dreams-pos-navmenu" x-cloak x-show="storeOpen" x-transition.opacity.scale.origin.top.right>
                        @foreach($availableBranches as $branch)
                            <form action="{{ route('tenant.switch-branch') }}" method="POST" style="margin:0;">
                                @csrf
                                <input type="hidden" name="branch" value="{{ $branch->id }}">
                                <button type="submit" class="dreams-pos-navmenu-item" style="{{ $activeBranch?->id === $branch->id ? 'background:#f0f9ff;font-weight:600;' : '' }}">
                                    {{ $branch->name }}
                                    @if($activeBranch?->id === $branch->id)
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left:auto;"><path d="M20 6 9 17l-5-5"/></svg>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                    @else
                    <span class="dreams-pos-store-btn" style="cursor:default;">
                        <span class="dreams-pos-store-icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10h18l-1.6-5.2A2.4 2.4 0 0 0 17.1 3H6.9a2.4 2.4 0 0 0-2.3 1.8L3 10Z"/><path d="M5 10v9h14v-9"/><path d="M9 19v-5h6v5"/></svg>
                        </span>
                        <span>{{ $activeBranch?->name ?? '' }}</span>
                    </span>
                    @endif
                </div>
                @endif

                <button type="button" class="dreams-pos-navbtn calc" aria-label="{{ __('pos.calculator') }}" title="{{ __('pos.calculator') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 7h8"/><path d="M8 11h.01"/><path d="M12 11h.01"/><path d="M16 11h.01"/><path d="M8 15h.01"/><path d="M12 15h.01"/><path d="M16 15h.01"/><path d="M8 19h.01"/><path d="M12 19h.01"/><path d="M16 19h.01"/></svg>
                </button>

                <button type="button" class="dreams-pos-navbtn icon" x-on:click="scannerOpen = ! scannerOpen" aria-label="Phone Scanner" title="Link Phone Scanner">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/></svg>
                </button>

                <button type="button" class="dreams-pos-navbtn icon" x-on:click="document.fullscreenElement ? document.exitFullscreen?.() : document.documentElement.requestFullscreen?.()" aria-label="{{ __('pos.fullscreen') }}" title="{{ __('pos.fullscreen') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
                </button>

                <a href="/tenant/sales-page" class="dreams-pos-navbtn icon" aria-label="{{ __('pos.sales_history') }}" title="{{ __('pos.sales_history') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                </a>

                <button type="button" class="dreams-pos-navbtn icon" aria-label="{{ __('pos.print') }}" title="{{ __('pos.print') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="7"/></svg>
                </button>

                <button type="button" class="dreams-pos-navbtn icon" aria-label="Sync" title="Sync">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 3v6h-6"/></svg>
                </button>

                <a href="/tenant/stock-dashboard" class="dreams-pos-navbtn icon" aria-label="{{ __('pos.statistics') }}" title="{{ __('pos.statistics') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-6"/></svg>
                </a>

                <a href="/tenant/manage-tax-and-efris" class="dreams-pos-navbtn icon" aria-label="{{ __('pos.settings') }}" title="{{ __('pos.settings') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="M19.4 15a1.8 1.8 0 0 0 .36 1.98l.05.05a2.15 2.15 0 1 1-3.04 3.04l-.05-.05a1.8 1.8 0 0 0-1.98-.36 1.8 1.8 0 0 0-1.1 1.65V21.4a2.15 2.15 0 1 1-4.3 0v-.08a1.8 1.8 0 0 0-1.1-1.65 1.8 1.8 0 0 0-1.98.36l-.05.05a2.15 2.15 0 1 1-3.04-3.04l.05-.05A1.8 1.8 0 0 0 4.6 15a1.8 1.8 0 0 0-1.65-1.1H2.85a2.15 2.15 0 1 1 0-4.3h.08A1.8 1.8 0 0 0 4.6 8.5a1.8 1.8 0 0 0-.36-1.98l-.05-.05a2.15 2.15 0 1 1 3.04-3.04l.05.05a1.8 1.8 0 0 0 1.98.36 1.8 1.8 0 0 0 1.1-1.65V2.1a2.15 2.15 0 1 1 4.3 0v.08a1.8 1.8 0 0 0 1.1 1.65 1.8 1.8 0 0 0 1.98-.36l.05-.05a2.15 2.15 0 1 1 3.04 3.04l-.05.05a1.8 1.8 0 0 0-.36 1.98 1.8 1.8 0 0 0 1.65 1.1h.08a2.15 2.15 0 1 1 0 4.3h-.08A1.8 1.8 0 0 0 19.4 15Z"/></svg>
                </a>

                <div class="dreams-pos-navprofile" x-on:click.outside="profileOpen = false">
                <button type="button" class="dreams-pos-navavatar" x-on:click="profileOpen = ! profileOpen" aria-label="{{ __('pos.user_profile') }}" title="{{ __('pos.user_profile') }}">
                    @if ($avatarUrl)
                        <img class="dreams-avatar" src="{{ $avatarUrl }}" alt="{{ $userName }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                        <span class="dreams-avatar-fallback" style="display:none;">{{ $initials }}</span>
                    @else
                        <span class="dreams-avatar-fallback">{{ $initials }}</span>
                    @endif
                    </button>
                    <div class="dreams-pos-navmenu" x-cloak x-show="profileOpen" x-transition.opacity.scale.origin.top.right>
                        @if ($dash['canAdmin'])
                        <a href="{{ route('filament.tenant.pages.admin-dashboard') }}" class="dreams-pos-navmenu-item">Dashboard</a>
                    @else
                        <button type="button" class="dreams-pos-navmenu-item" x-on:click="profileOpen = false; dashboardOpen = true">Dashboard</button>
                    @endif
                        <form action="{{ route('filament.tenant.auth.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dreams-pos-navmenu-item">@lang('pos.logout')</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="dreams-modal-overlay" x-cloak x-show="dashboardOpen" x-on:click.self="dashboardOpen = false" x-transition.opacity>
                <div class="dreams-modal" wire:key="not-authorized-modal">
                    <div class="dreams-modal-head">
                        <h3>@lang('pos.access_restricted')</h3>
                        <button type="button" class="dreams-modal-close" x-on:click="dashboardOpen = false">&times;</button>
                    </div>
                    <div class="dreams-modal-body">
                        <p style="margin:0 0 8px;color:#334155;font-size:14px;line-height:1.6;">@lang('pos.access_restricted_msg')</p>
                        <p style="margin:0;color:#64748b;font-size:13px;line-height:1.6;">@lang('pos.access_restricted_msg2')</p>
                        <div style="display:flex;gap:10px;margin-top:20px;">
                            @if ($dash['allowed'])
                                <a href="{{ $dash['allowed']['url'] }}" class="dreams-action-btn pay" style="padding:0 18px;grid-column:auto;text-decoration:none;">@lang('pos.continue')</a>
                            @endif
                            <button type="button" class="dreams-action-btn clear" style="padding:0 18px;" x-on:click="dashboardOpen = false">@lang('pos.close')</button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        {{-- Phone Scanner QR Code Modal --}}
        @php
            $scannerIp = '127.0.0.1';
            foreach (net_get_interfaces() as $iface) {
                if (!isset($iface['ipv4'])) continue;
                $addr = $iface['ipv4']['address'] ?? '';
                if (str_starts_with($addr, '192.168.')) { $scannerIp = $addr; break; }
                if ($addr && $addr !== '127.0.0.1') { $scannerIp = $addr; }
            }
        @endphp
        <template x-if="scannerOpen">
        <div class="scanner-modal-overlay" x-on:click.self="scannerOpen = false">
            <div style="background:#fff;border-radius:20px;padding:32px;max-width:400px;width:90%;box-shadow:0 25px 60px rgba(0,0,0,0.3);text-align:center;" @click.stop>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <h3 style="margin:0;font-size:18px;font-weight:700;color:#1e293b;">Phone Scanner</h3>
                    <button type="button" x-on:click="scannerOpen = false" style="background:none;border:none;font-size:24px;cursor:pointer;color:#94a3b8;">&times;</button>
                </div>
                <p style="margin:0 0 16px;font-size:13px;color:#64748b;">Scan this QR code with your phone camera to link it as a barcode scanner.</p>
                <div style="display:inline-block;padding:16px;background:#fff;border:2px solid #e2e8f0;border-radius:12px;margin-bottom:16px;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode('POS_CONNECT:ws://' . $scannerIp . ':8432/ws') }}&color=1e293b" alt="Scanner QR Code" style="display:block;width:200px;height:200px;">
                </div>
                <p style="margin:0 0 6px;font-size:13px;color:#475569;font-weight:600;">ws://{{ $scannerIp }}:8432/ws</p>
                <p style="margin:0 0 16px;font-size:11px;color:#94a3b8;">Make sure the WebSocket server is running: <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;">php scanner:websocket</code></p>
                <div style="display:flex;gap:8px;justify-content:center;">
                    <button type="button" x-on:click="navigator.clipboard.writeText('http://{{ $scannerIp }}:8432')" style="padding:10px 20px;border-radius:8px;border:1px solid #e2e8f0;background:#f8fafc;color:#475569;font-size:13px;font-weight:600;cursor:pointer;">Copy URL</button>
                    <button type="button" x-on:click="scannerOpen = false" style="padding:10px 20px;border-radius:8px;border:none;background:#6366f1;color:#fff;font-size:13px;font-weight:600;cursor:pointer;">Done</button>
                </div>
            </div>
        </div>
        </template>

        <div class="dreams-pos-shell">
            <section class="dreams-pos-left">
                <div class="dreams-pos-toolbar">
                    <div class="dreams-pos-title">
                        <h1>@lang('pos.pos')</h1>
                    </div>
                    <div class="dreams-pos-actions">
                        <span class="dreams-pos-chip">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            {{ now()->format('H:i') }}
                        </span>
                        <a href="/tenant/sales-page" class="dreams-pos-icon-btn has-label" title="{{ __('pos.sales_history') }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>
                            @lang('pos.sales_history')
                        </a>
                        <button type="button" wire:click="openCashInModal" class="dreams-pos-icon-btn has-label" title="{{ __('pos.cash_in') }}" style="border-color:#d1fae5;color:#059669;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                            @lang('pos.cash_in')
                        </button>
                        <button type="button" wire:click="openCashOutModal" class="dreams-pos-icon-btn has-label" title="{{ __('pos.cash_out') }}" style="border-color:#fee2e2;color:#dc2626;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                            @lang('pos.cash_out')
                        </button>
                        <button type="button" wire:click="openXReport" class="dreams-pos-icon-btn has-label" title="{{ __('pos.x_report') }}" style="border-color:#e0e7ff;color:#4f46e5;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                            @lang('pos.x_report')
                        </button>
                        <button type="button" wire:click="openZReport" class="dreams-pos-icon-btn has-label" title="{{ __('pos.z_report') }}" style="border-color:#fef3c7;color:#d97706;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><circle cx="12" cy="15" r="3"/></svg>
                            @lang('pos.z_report')
                        </button>
                    </div>
                </div>

                <div class="dreams-pos-filterbar">
                    <label class="dreams-pos-search">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        <input type="search" wire:model.live.debounce.250ms="search" placeholder="@lang('pos.search_product')">
                    </label>

                    <div class="dreams-categories">
                        <button type="button" wire:click="$set('selectedCategory', 'all')" class="dreams-category-btn {{ $selectedCategory === 'all' ? 'is-active' : '' }}">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h7v7H4z"/><path d="M13 6h7v7h-7z"/><path d="M4 15h7v3H4z"/><path d="M13 15h7v3h-7z"/></svg>
                            @lang('pos.all_categories')
                        </button>
                        @foreach ($categories as $category)
                            <button type="button" wire:key="category-{{ $category->id }}" wire:click="$set('selectedCategory', '{{ $category->id }}')" class="dreams-category-btn {{ $selectedCategory === $category->id ? 'is-active' : '' }}">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="dreams-products-wrap">
                    <div class="dreams-products-grid">
                        @forelse ($products as $product)
                            @php
                                $price = (float) ($product->billing_price ?? $product->selling_price ?? 0);
                                $activeBranchId = $activeBranch?->id ?? null;
                                $inventory = app(\App\Services\InventoryService::class);
                                if ($product->track_serial_numbers) {
                                    $stock = $activeBranchId
                                        ? $product->serials()->where('status', 'available')->where('branch_id', $activeBranchId)->count()
                                        : $product->availableSerialCount();
                                } else {
                                    $stock = $activeBranchId
                                        ? $inventory->getProductStock($product->id, $activeBranchId)
                                        : (float) ($product->current_stock ?? 0);
                                }
                                $hasVariants = $product->has_variants;
                                $variantCount = $hasVariants ? $product->variants->where('is_active', true)->count() : 0;
                                if ($hasVariants) {
                                    $prices = $product->variants->where('is_active', true)->pluck('effective_selling_price')->filter();
                                    $stock = $product->variants->where('is_active', true)->sum(function ($v) use ($activeBranchId, $inventory) {
                                        if ($v->track_serial_numbers) {
                                            return $v->serials()->where('status', 'available')->count();
                                        }
                                        return $activeBranchId
                                            ? $inventory->getProductStock($v->product_id, $activeBranchId, $v->id)
                                            : (float) $v->current_stock;
                                    });
                                    $price = $prices->count() > 0 ? $prices->min() : $price;
                                }
                            @endphp
                            <button type="button" wire:key="product-{{ $product->id }}" wire:click="addToCart('{{ $product->id }}')" class="dreams-product-card">
                                <div class="dreams-product-image">
                                    @if (! empty($product->imageUrl()))
                                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}">
                                    @else
                                        <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-9-5-9 5 9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
                                    @endif
                                    <span class="dreams-product-badge">{{ $product->category?->name ?? __('pos.uncategorized') }}</span>
                                </div>
                                <div class="dreams-product-body">
                                    <p class="dreams-product-name">{{ $product->name }}</p>
                                    @if ($hasVariants)
                                        <div style="font-size:10px;color:#2563eb;font-weight:600;margin-top:2px;">{{ $variantCount }} variant{{ $variantCount > 1 ? 's' : '' }}</div>
                                    @endif
                                    <div class="dreams-product-code">{{ $product->barcode ?: ($product->sku ?: 'No barcode') }}</div>
                                    <div class="dreams-product-foot">
                                        <div class="dreams-product-price">{{ $currency }} {{ number_format($price, 0) }}{{ $hasVariants ? '+' : '' }}</div>
                                        <div class="dreams-product-stock">@lang('pos.stock')<br>{{ number_format($stock, 0) }}</div>
                                    </div>
                                </div>
                            </button>
                        @empty
                            <div class="dreams-cart-empty" style="grid-column: 1 / -1;">
                                <div>
                                    <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                    <strong>@lang('pos.no_products')</strong>
                                    <span>@lang('pos.try_another')</span>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <aside class="dreams-pos-cart">
                <div class="dreams-cart-head">
                    <div class="dreams-cart-title-row">
                        <h2 class="dreams-cart-title">@lang('pos.order_list')</h2>
                        <div class="dreams-pos-actions" style="gap:8px;">
                            <span class="dreams-cart-count">{{ number_format($cartCount, 0) }} @lang('pos.items')</span>
                            <button type="button" wire:click="clearCart" class="dreams-pos-icon-btn" title="Clear Cart" @disabled(empty($cart))>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                            </button>
                            <button type="button" wire:click="openOrdersModal('paid')" class="dreams-pos-icon-btn has-label {{ $showOrdersModal ? 'is-active' : '' }}" title="{{ __('pos.view_orders') }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                @lang('pos.view_orders')
                            </button>
                        </div>
                    </div>
                    <div class="dreams-cart-form">
                        <div style="display:flex;gap:8px;align-items:center;">
                            <select wire:model.live="customerId" class="dreams-select" aria-label="Customer" style="flex:1 1 auto;min-width:0;">
                                <option value="">@lang('pos.walk_in_customer')</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->full_name }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="openAddCustomerModal" class="dreams-add-customer-btn" title="Add Customer">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                            </button>
                        </div>
                        <select wire:model.live="paymentMethodId" class="dreams-select" aria-label="Payment method">
                            @foreach ($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="dreams-cart-items">
                    @forelse ($cart as $cartKey => $item)
                        <div class="dreams-cart-item" wire:key="cart-{{ $cartKey }}">
                            <div class="dreams-cart-thumb">
                                @if (! empty($item['image']))
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" />
                                @else
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-9-5-9 5 9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
                                @endif
                            </div>
                            <div>
                                <p class="dreams-cart-name">{{ $item['name'] }}</p>
                                @if (!empty($item['variant_name']))
                                    <div style="font-size:10px;font-weight:600;color:#2563eb;margin-top:1px;">{{ $item['variant_name'] }}</div>
                                @endif
                                @if (!empty($item['serial_number']))
                                    <div style="font-size:10px;color:var(--pos-muted);margin-top:1px;">@lang('pos.serial_number'): {{ $item['serial_number'] }}</div>
                                @endif
                                <div class="dreams-cart-meta">{{ $currency }} {{ number_format((float) $item['price'], 0) }} / {{ $item['category'] }}</div>
                                <div class="dreams-qty">
                                    <button type="button" wire:click="decreaseQuantity('{{ $cartKey }}')">-</button>
                                    <span>{{ number_format((float) $item['quantity'], 0) }}</span>
                                    <button type="button" wire:click="increaseQuantity('{{ $cartKey }}')">+</button>
                                </div>
                            </div>
                            <div>
                                <div class="dreams-line-total">{{ $currency }} {{ number_format((float) $item['price'] * (float) $item['quantity'], 0) }}</div>
                                <button type="button" wire:click="removeFromCart('{{ $cartKey }}')" class="dreams-remove" title="Remove item">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="dreams-cart-empty">
                            <div>
                                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4h2l2.2 10.5a2 2 0 0 0 2 1.5h7.9a2 2 0 0 0 1.9-1.4L21 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                                <strong>@lang('pos.no_products_selected')</strong>
                                <span>@lang('pos.click_product')</span>
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="dreams-summary">
                    <div class="dreams-summary-row">
                        <span>@lang('pos.sub_total')</span>
                        <strong>{{ $currency }} {{ number_format($this->subtotal, 0) }}</strong>
                    </div>
                    <div class="dreams-summary-row">
                        <span>@lang('pos.tax')</span>
                        <strong>{{ $currency }} {{ number_format($this->taxAmount, 0) }}</strong>
                    </div>
                    <div class="dreams-discount-row">
                        <div class="dreams-summary-row" style="padding:0;">
                            <span>@lang('pos.discount')</span>
                            <strong>- {{ $currency }} {{ number_format($this->discountAmount, 0) }}</strong>
                        </div>
                        <input type="number" min="0" wire:model.live.debounce.250ms="orderDiscount" class="dreams-number-input" placeholder="@lang('pos.discount')">
                    </div>

                    <div class="dreams-total-row">
                        <span>@lang('pos.total_payable')</span>
                        <strong>{{ $currency }} {{ number_format($this->totalAmount, 0) }}</strong>
                    </div>

                    <div class="dreams-checkout-actions">
                        <button type="button" wire:click="holdOrder" wire:loading.attr="disabled" class="dreams-action-btn hold" @disabled(empty($cart))>
                            @lang('pos.hold')
                        </button>
                        <button type="button" wire:click="clearCart" class="dreams-action-btn void" @disabled(empty($cart))>
                            @lang('pos.void')
                        </button>
                        <button type="button" wire:click="openPaymentModal" class="dreams-action-btn pay" @disabled(empty($cart))>
                            <span>@lang('pos.payment')</span>
                        </button>
                    </div>

                    @if ($lastReceiptNumber)
                        <div class="dreams-summary-row" style="margin-top:10px;justify-content:center;color:#047857;">
                            @lang('pos.last_receipt') <strong>{{ $lastReceiptNumber }}</strong>
                        </div>
                    @endif
                </div>
            </aside>
        </div>

        @if ($showOrdersModal)
            <div class="dreams-modal-overlay" wire:click.self="$set('showOrdersModal', false)">
                <div class="dreams-modal wide" wire:key="orders-modal-{{ $renderKey }}">
                    <div class="dreams-modal-head">
                        <h3>@lang('pos.view_orders')</h3>
                        <button type="button" class="dreams-modal-close" wire:click="$set('showOrdersModal', false)">&times;</button>
                    </div>
                    <div class="dreams-modal-tabs">
                        <button type="button" class="dreams-modal-tab {{ $ordersTab === 'paid' ? 'is-active' : '' }}" wire:click="setOrdersTab('paid')">@lang('pos.paid')</button>
                        <button type="button" class="dreams-modal-tab {{ $ordersTab === 'unpaid' ? 'is-active' : '' }}" wire:click="setOrdersTab('unpaid')">@lang('pos.unpaid')</button>
                        <button type="button" class="dreams-modal-tab {{ $ordersTab === 'held' ? 'is-active' : '' }}" wire:click="setOrdersTab('held')">@lang('pos.on_hold')</button>
                    </div>
                    <div class="dreams-modal-body">
                        <div class="dreams-order-table">
                            @forelse ($this->orders()[$ordersTab] as $order)
                                <div class="dreams-order-row" wire:key="order-{{ $order['id'] }}">
                                    <div class="dreams-order-main">
                                        <p class="dreams-order-no">{{ $order['receipt_number'] }}</p>
                                        <div class="dreams-order-meta">
                                            {{ $order['customer_name'] }} &middot; {{ $order['item_count'] }} item(s) &middot;
                                            {{ $order['transaction_date']?->format('M d, H:i') }}
                                        </div>
                                    </div>
                                    <div class="dreams-order-total">
                                        {{ $currency }} {{ number_format($order['total_amount'], 0) }}
                                        @if ($ordersTab === 'unpaid')
                                            <div style="font-size:10px;color:#b91c1c;margin-top:2px;">@lang('pos.due') {{ $currency }} {{ number_format($order['balance_due'], 0) }}</div>
                                        @endif
                                    </div>
                                    <div class="dreams-order-actions">
                                        @if ($ordersTab === 'paid')
                                            <a href="/tenant/invoice-details/{{ $order['id'] }}" class="dreams-order-btn">@lang('pos.view')</a>
                                        @elseif ($ordersTab === 'unpaid')
                                            <a href="/tenant/invoice-details/{{ $order['id'] }}" class="dreams-order-btn">@lang('pos.view')</a>
                                            <input type="number" min="0" step="1" wire:model.debounce.300ms="collectAmounts.{{ $order['id'] }}" placeholder="@lang('pos.due') {{ number_format($order['balance_due'], 0) }}" class="dreams-collect-input" title="{{ __('pos.amount_to_collect') }}">
                                            <button type="button" wire:click="collectOrderPayment('{{ $order['id'] }}')" class="dreams-order-btn resume">@lang('pos.collect')</button>
                                        @else
                                            <button type="button" wire:click="resumeHeldOrder('{{ $order['id'] }}')" class="dreams-order-btn resume">@lang('pos.resume')</button>
                                            <button type="button" wire:click="voidHeldOrder('{{ $order['id'] }}')" class="dreams-order-btn danger">@lang('pos.void')</button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="dreams-cart-empty">
                                    <div>
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4h2l2.2 10.5a2 2 0 0 0 2 1.5h7.9a2 2 0 0 0 1.9-1.4L21 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                                        <strong>@lang('pos.no_orders', ['tab' => $ordersTab])</strong>
                                        <span>@lang('pos.nothing_to_show')</span>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($showAddCustomerModal)
            <div class="dreams-modal-overlay" wire:click.self="$set('showAddCustomerModal', false)">
                <div class="dreams-modal" wire:key="add-customer-modal">
                    <div class="dreams-modal-head">
                        <h3>@lang('pos.add_customer')</h3>
                        <button type="button" class="dreams-modal-close" wire:click="$set('showAddCustomerModal', false)">&times;</button>
                    </div>
                    <div class="dreams-modal-body">
                        <div class="dreams-modal-field">
                            <label>@lang('pos.full_name')</label>
                            <input type="text" wire:model="newCustomerName" placeholder="@lang('pos.customer_name')">
                        </div>
                        <div class="dreams-modal-grid">
                            <div class="dreams-modal-field">
                                <label>@lang('pos.phone')</label>
                                <input type="text" wire:model="newCustomerPhone" placeholder="@lang('pos.phone')">
                            </div>
                            <div class="dreams-modal-field">
                                <label>@lang('pos.email')</label>
                                <input type="email" wire:model="newCustomerEmail" placeholder="@lang('pos.email')">
                            </div>
                        </div>
                        <div class="dreams-modal-grid">
                            <div class="dreams-modal-field">
                                <label>@lang('pos.tin')</label>
                                <input type="text" wire:model="newCustomerTin" placeholder="@lang('pos.tin')">
                            </div>
                            <div class="dreams-modal-field">
                                <label>@lang('pos.address')</label>
                                <input type="text" wire:model="newCustomerAddress" placeholder="@lang('pos.address')">
                            </div>
                        </div>
                        <div class="dreams-pay-actions">
                            <button type="button" class="dreams-action-btn clear" wire:click="$set('showAddCustomerModal', false)">@lang('pos.cancel')</button>
                            <button type="button" wire:click="saveCustomer" class="dreams-action-btn pay">@lang('pos.save_customer')</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($showVariantModal)
            <div class="dreams-modal-overlay" wire:click.self="$set('showVariantModal', false)">
                <div class="dreams-modal" wire:key="variant-modal" style="max-width:650px;">
                    <div class="dreams-modal-head">
                        <h3>@lang('pos.select_variant') — {{ $variantModalProductName }}</h3>
                        <button type="button" class="dreams-modal-close" wire:click="$set('showVariantModal', false)">&times;</button>
                    </div>
                    <div class="dreams-modal-body" style="padding:16px;">
                        @if (count($variantModalVariants) > 0)
                            <div style="display:flex;flex-direction:column;gap:8px;max-height:400px;overflow-y:auto;">
                                @foreach ($variantModalVariants as $variant)
                                    @php $isSelected = $selectedVariantId === $variant['id']; @endphp
                                    <div wire:click="selectVariant('{{ $variant['id'] }}')" style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:2px solid {{ $isSelected ? 'var(--pos-orange)' : 'var(--pos-border)' }};border-radius:10px;cursor:pointer;background:{{ $isSelected ? '#fff7ed' : '#fff' }};transition:all 0.15s;{{ $variant['available_stock'] <= 0 ? 'opacity:0.5;pointer-events:none;' : '' }}">
                                        <div style="width:22px;height:22px;border:2px solid {{ $isSelected ? 'var(--pos-orange)' : '#cbd5e1' }};border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            @if ($isSelected)
                                                <div style="width:12px;height:12px;border-radius:50%;background:var(--pos-orange);"></div>
                                            @endif
                                        </div>
                                        <div style="flex:1;">
                                            <div style="font-size:14px;font-weight:700;color:#0f172a;">{{ $variant['combination'] }}</div>
                                            <div style="font-size:11px;color:var(--pos-muted);margin-top:2px;">
                                                @lang('pos.sku') {{ $variant['sku'] ?? '—' }}
                                                @if ($variant['track_serial_numbers'])
                                                    &middot; <span style="color:#2563eb;">@lang('pos.serialized')</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div style="text-align:right;">
                                            <div style="font-size:14px;font-weight:700;color:var(--pos-navy);">{{ $currency }} {{ number_format($variant['effective_selling_price'], 0) }}</div>
                                            <div style="font-size:11px;color:{{ $variant['available_stock'] > 0 ? 'var(--pos-muted)' : '#dc2626' }};">
                                                @lang('pos.stock') {{ number_format($variant['available_stock'], 0) }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="text-align:center;padding:40px;color:var(--pos-muted);">
                                <p>@lang('pos.no_variants')</p>
                            </div>
                        @endif
                    </div>
                    <div class="dreams-modal-actions" style="padding:14px 20px;border-top:1px solid var(--pos-border);display:flex;justify-content:flex-end;gap:8px;">
                        <button type="button" class="dreams-action-btn clear" wire:click="$set('showVariantModal', false)">@lang('pos.cancel')</button>
                        <button type="button" class="dreams-action-btn primary" wire:click="confirmVariantSelection" @disabled(empty($selectedVariantId))>@lang('pos.add_to_cart')</button>
                    </div>
                </div>
            </div>
        @endif

        @if ($showSerialModal)
            <div class="dreams-modal-overlay" wire:click.self="$set('showSerialModal', false)">
                <div class="dreams-modal" wire:key="serial-modal">
                    <div class="dreams-modal-head">
                        <h3>@lang('pos.select_serial') — {{ $serialModalProductName }}</h3>
                        <button type="button" class="dreams-modal-close" wire:click="$set('showSerialModal', false)">&times;</button>
                    </div>
                    <div class="dreams-modal-body" style="padding:16px;">
                        <input type="text" wire:model.live="serialSearch" placeholder="@lang('pos.search_serial')" style="width:100%;padding:10px 12px;border:1px solid var(--pos-border);border-radius:8px;font-size:13px;margin-bottom:12px;outline:none;">
                        <p style="font-size:12px;color:var(--pos-muted);margin-bottom:10px;">{{ count($serialModalAvailable) }} @lang('pos.available') &middot; {{ count($serialModalSelected) }} @lang('pos.selected')</p>
                        <div style="max-height:320px;overflow-y:auto;display:flex;flex-direction:column;gap:6px;">
                            @forelse ($this->filteredSerials as $serial)
                                @php $isSelected = in_array($serial['id'], $serialModalSelected); @endphp
                                <div wire:click="toggleSerial('{{ $serial['id'] }}')" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid {{ $isSelected ? 'var(--pos-orange)' : 'var(--pos-border)' }};border-radius:8px;cursor:pointer;background:{{ $isSelected ? '#fff7ed' : '#fff' }};transition:all 0.15s;">
                                    <div style="width:20px;height:20px;border:2px solid {{ $isSelected ? 'var(--pos-orange)' : '#cbd5e1' }};border-radius:4px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        @if ($isSelected)
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--pos-orange)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                        @endif
                                    </div>
                                    <div style="flex:1;">
                                        <div style="font-size:13px;font-weight:600;color:#0f172a;">{{ $serial['serial_number'] }}</div>
                                        @if (!empty($serial['box_serial_number']))
                                            <div style="font-size:11px;color:var(--pos-muted);">@lang('pos.box') {{ $serial['box_serial_number'] }}</div>
                                        @endif
                                    </div>
                                    <div style="font-size:13px;font-weight:600;color:var(--pos-navy);">{{ $currency }} {{ number_format($serial['selling_price'], 0) }}</div>
                                </div>
                            @empty
                                <div style="text-align:center;padding:30px;color:var(--pos-muted);font-size:13px;">@lang('pos.no_serials')</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="dreams-modal-actions" style="padding:14px 20px;border-top:1px solid var(--pos-border);display:flex;justify-content:flex-end;gap:8px;">
                        <button type="button" class="dreams-action-btn clear" wire:click="$set('showSerialModal', false)">@lang('pos.cancel')</button>
                        <button type="button" class="dreams-action-btn primary" wire:click="confirmSerialSelection">@lang('pos.add_selected') ({{ count($serialModalSelected) }})</button>
                    </div>
                </div>
            </div>
        @endif

        @if ($showPaymentModal)
            <div class="dreams-modal-overlay" wire:click.self="$set('showPaymentModal', false)">
                <div class="dreams-modal" wire:key="payment-modal">
                    <div class="dreams-modal-head">
                        <h3>@lang('pos.payment')</h3>
                        <button type="button" class="dreams-modal-close" wire:click="$set('showPaymentModal', false)">&times;</button>
                    </div>
                    <div class="dreams-modal-body">
                        <div class="dreams-pay-summary">
                            <div class="dreams-summary-row">
                                <span>@lang('pos.sub_total')</span>
                                <strong>{{ $currency }} {{ number_format($this->subtotal, 0) }}</strong>
                            </div>
                            <div class="dreams-summary-row">
                                <span>@lang('pos.tax')</span>
                                <strong>{{ $currency }} {{ number_format($this->taxAmount, 0) }}</strong>
                            </div>
                            <div class="dreams-summary-row">
                                <span>@lang('pos.discount')</span>
                                <strong>- {{ $currency }} {{ number_format($this->discountAmount, 0) }}</strong>
                            </div>
                            <div class="dreams-pay-total">
                        <span>@lang('pos.total_payable')</span>
                                <strong>{{ $currency }} {{ number_format($this->totalAmount, 0) }}</strong>
                            </div>
                        </div>

                        @if ($selectedCustomer = $this->selectedCustomer())
                            @php
                                $loyaltyBalance = app(\App\Services\CustomerLoyaltyService::class)->getBalance(auth()->user()->tenant_id, $selectedCustomer->id);
                                $walletBalance = app(\App\Services\CustomerWalletService::class)->getBalance(auth()->user()->tenant_id, $selectedCustomer->id);
                            @endphp
                            <div style="display:flex;gap:8px;margin-bottom:8px;">
                                @if($loyaltyBalance > 0)
                                    <div style="flex:1;padding:6px 10px;background:#fef3c7;border:1px solid #fbbf24;border-radius:8px;font-size:11px;color:#92400e;">
                                        <strong>Star</strong> {{ number_format($loyaltyBalance) }} pts
                                    </div>
                                @endif
                                @if($walletBalance > 0)
                                    <div style="flex:1;padding:6px 10px;background:#d1fae5;border:1px solid #34d399;border-radius:8px;font-size:11px;color:#065f46;">
                                        <strong>Wallet</strong> {{ $currency }} {{ number_format($walletBalance, 0) }}
                                    </div>
                                @endif
                            </div>
                            <div class="dreams-pay-type-row">
                                <button type="button" class="dreams-pay-type-btn {{ $paymentType === 'cash' ? 'is-active' : '' }}" wire:click="setPaymentType('cash')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg>
                                    @lang('pos.cash')
                                </button>
                                <button type="button" class="dreams-pay-type-btn {{ $paymentType === 'credit' ? 'is-active' : '' }}" wire:click="setPaymentType('credit')" title="{{ $selectedCustomer->credit_enabled ? __('pos.credit_tip') : __('pos.credit_disabled') }}" style="opacity:{{ $selectedCustomer->credit_enabled ? '1' : '.45' }};cursor:{{ $selectedCustomer->credit_enabled ? 'pointer' : 'not-allowed' }};">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                                    @lang('pos.credit')
                                </button>
                                <button type="button" class="dreams-pay-type-btn {{ $paymentType === 'layaway' ? 'is-active' : '' }}" wire:click="setPaymentType('layaway')" title="{{ __('pos.layaway_tip') }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                    @lang('pos.layaway')
                                </button>
                                @if($walletBalance > 0)
                                <button type="button" class="dreams-pay-type-btn {{ $paymentType === 'wallet' ? 'is-active' : '' }}" wire:click="setPaymentType('wallet')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                                    Wallet
                                </button>
                                @endif
                            </div>
                        @endif

                        @if ($paymentType === 'credit')
                            @php
                                $customer = $selectedCustomer;
                                $availableCredit = $customer ? max(0, (float) $customer->credit_limit - (float) $customer->balance) : 0;
                            @endphp
                            <div class="dreams-credit-note">
                                <strong>{{ $customer?->full_name }}</strong> @lang('pos.buying_on_account')<br>
                                @lang('pos.credit_limit') {{ $currency }} {{ number_format((float) $customer?->credit_limit, 0) }} &middot; @lang('pos.current_balance') {{ $currency }} {{ number_format((float) $customer?->balance, 0) }}<br>
                                @lang('pos.available_credit') <strong>{{ $currency }} {{ number_format($availableCredit, 0) }}</strong>.<br>
                                @lang('pos.no_payment_now')
                            </div>
                        @elseif ($paymentType === 'layaway')
                            <div style="display:flex;flex-direction:column;gap:12px;">
                                <div class="dreams-modal-field" style="margin:0;">
                                    <label>@lang('pos.payment_method_deposit')</label>
                                    <select wire:model="paymentMethodId">
                                        @foreach ($paymentMethods as $method)
                                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="dreams-modal-grid">
                                    <div class="dreams-modal-field">
                                        <label>@lang('pos.deposit_paid_today')</label>
                                        <input type="number" min="0" step="0.01" wire:model.live.debounce.250ms="amountReceived" class="dreams-number-input" placeholder="@lang('pos.deposit_amount')">
                                    </div>
                                    <div class="dreams-modal-field">
                                        <label>@lang('pos.due_date')</label>
                                        <input type="date" wire:model="layawayDueDate">
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="dreams-split-toggle" wire:click="toggleSplitPayment">
                                <span class="dreams-split-label">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
                                    @lang('pos.split_payment')
                                </span>
                                <span class="dreams-switch {{ $splitPayment ? 'is-on' : '' }}"></span>
                            </div>

                            @if ($splitPayment)
                                <div class="dreams-split-rows">
                                    @foreach ($splitPayments as $index => $split)
                                        <div class="dreams-split-row" wire:key="split-{{ $index }}">
                                            <select wire:model="splitPayments.{{ $index }}.payment_method_id" aria-label="Payment method">
                                                @foreach ($paymentMethods as $method)
                                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="number" min="0" step="0.01" wire:model.live.debounce.250ms="splitPayments.{{ $index }}.amount" placeholder="@lang('pos.amount')">
                                            <button type="button" class="dreams-split-remove" wire:click="removeSplitPaymentRow({{ $index }})" title="{{ __('pos.remove') }}">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="button" wire:click="addSplitPaymentRow" class="dreams-split-add">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                    @lang('pos.add_payment_method')
                                </button>

                                <div class="dreams-split-total">
                                    <span>@lang('pos.total_received')</span>
                                    <strong>{{ $currency }} {{ number_format($this->splitTotal, 0) }}</strong>
                                </div>
                            @else
                                <div class="dreams-payment-methods">
                                    @foreach ($paymentMethods as $method)
                                        <label class="dreams-pay-radio" wire:key="pay-{{ $method->id }}">
                                            <input type="radio" wire:model.live="paymentMethodId" value="{{ $method->id }}">
                                            <span>{{ $method->name }}</span>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="dreams-modal-field" style="margin-top:14px;">
                                    <label>@lang('pos.amount_received')</label>
                                    <input type="number" min="0" step="0.01" wire:model.live.debounce.250ms="amountReceived" class="dreams-number-input" placeholder="@lang('pos.amount_received')">
                                </div>
                            @endif
                        @endif

                        @if ($paymentType === 'cash')
                            <div class="dreams-pay-summary" style="margin-top:4px;">
                                <div class="dreams-summary-row">
                                    <span>@lang('pos.change')</span>
                                    <strong>{{ $currency }} {{ number_format($this->changeDue, 0) }}</strong>
                                </div>
                            </div>
                        @elseif ($paymentType === 'layaway')
                            <div class="dreams-pay-summary" style="margin-top:4px;">
                                <div class="dreams-summary-row">
                                    <span>@lang('pos.balance_due')</span>
                                    <strong style="color:#b91c1c;">{{ $currency }} {{ number_format(max(0, $this->totalAmount - $this->receivedAmount()), 0) }}</strong>
                                </div>
                            </div>
                        @endif

                        <div class="dreams-pay-actions">
                            <button type="button" class="dreams-action-btn clear" wire:click="$set('showPaymentModal', false)">@lang('pos.cancel')</button>
                            <button type="button" wire:click="checkout" wire:loading.attr="disabled" class="dreams-action-btn pay">
                                <span wire:loading.remove wire:target="checkout">
                                    {{ $paymentType === 'credit' ? __('pos.save_credit_sale') : ($paymentType === 'layaway' ? __('pos.save_layaway') : __('pos.complete_sale')) }}
                                </span>
                                <span wire:loading wire:target="checkout">@lang('pos.saving_sale')</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @if ($showReceiptModal && ($receipt = $this->receipt()))
            <div class="dreams-modal-overlay">
                <div class="dreams-modal dreams-receipt-modal" wire:key="receipt-modal">
                    <div class="dreams-modal-head">
                        <h3>@lang('pos.receipt_preview')</h3>
                        <button type="button" class="dreams-modal-close" wire:click="closeReceiptModal">&times;</button>
                    </div>
                    <div class="dreams-modal-body" style="background:#f1f5f9;padding:18px;">
                        <div class="dreams-receipt dreams-receipt-printable">
                            <div class="dreams-receipt-center">
                                <h2>{{ $receipt['company']['name'] }}</h2>
                                @if (! empty($receipt['company']['address']))
                                    <p>{{ $receipt['company']['address'] }}</p>
                                @endif
                                @if (! empty($receipt['company']['phone']))
                                    <p>@lang('pos.tel') {{ $receipt['company']['phone'] }}</p>
                                @endif
                                @if (! empty($receipt['company']['tin']))
                                    <p>@lang('pos.tin') {{ $receipt['company']['tin'] }}</p>
                                @endif
                            </div>

                            <hr>

                            <p><strong>@lang('pos.receipt_no')</strong> {{ $receipt['transaction']->receipt_number }}</p>
                            <p><strong>@lang('pos.date')</strong> {{ $receipt['transaction']->transaction_date?->format('d M Y, H:i') }}</p>
                            <p><strong>@lang('pos.branch')</strong> {{ $receipt['branch'] ?? '—' }}</p>
                            <p><strong>@lang('pos.cashier')</strong> {{ $receipt['cashier'] }}</p>
                            <p><strong>@lang('pos.customer')</strong> {{ $receipt['customer']?->full_name ?? $receipt['transaction']->customer_name ?? __('pos.walk_in_customer') }}</p>

                            <hr>

                            <table class="dreams-receipt-table">
                                <thead>
                                    <tr>
                                        <th>@lang('pos.item')</th>
                                        <th>@lang('pos.qty')</th>
                                        <th>@lang('pos.amount')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($receipt['items'] as $item)
                                        <tr>
                                            <td class="dreams-receipt-item-name">{{ $item->product_name }}</td>
                                            <td class="dreams-receipt-qty">{{ number_format((float) $item->quantity, 0) }} x {{ number_format((float) $item->unit_price, 0) }}</td>
                                            <td>{{ $receipt['company']['currency'] }} {{ number_format((float) $item->line_total, 0) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <hr>

                            <div class="dreams-receipt-line">
                                <span>@lang('pos.sub_total')</span>
                                <strong>{{ $receipt['company']['currency'] }} {{ number_format((float) $receipt['transaction']->subtotal, 0) }}</strong>
                            </div>
                            <div class="dreams-receipt-line">
                                <span>@lang('pos.tax')</span>
                                <strong>{{ $receipt['company']['currency'] }} {{ number_format((float) $receipt['transaction']->tax_amount, 0) }}</strong>
                            </div>
                            <div class="dreams-receipt-line">
                                <span>@lang('pos.discount')</span>
                                <strong>- {{ $receipt['company']['currency'] }} {{ number_format((float) $receipt['transaction']->discount_amount, 0) }}</strong>
                            </div>
                            <div class="dreams-receipt-grand">
                                <span>@lang('pos.total')</span>
                                <strong>{{ $receipt['company']['currency'] }} {{ number_format((float) $receipt['transaction']->total_amount, 0) }}</strong>
                            </div>

                            @foreach ($receipt['payments'] as $payment)
                                <div class="dreams-receipt-line">
                                    <span>@lang('pos.paid') ({{ $payment->payment_method }})</span>
                                    <strong>{{ $receipt['company']['currency'] }} {{ number_format((float) $payment->amount_paid, 0) }}</strong>
                                </div>
                            @endforeach

                            <div class="dreams-receipt-line">
                                <span>@lang('pos.change')</span>
                                <strong>{{ $receipt['company']['currency'] }} {{ number_format((float) $receipt['transaction']->change_given, 0) }}</strong>
                            </div>

                            @if ((float) $receipt['transaction']->balance_due > 0)
                                <div class="dreams-receipt-line" style="color:#b91c1c;font-weight:700;">
                                    <span>@lang('pos.balance_due')</span>
                                    <strong>{{ $receipt['company']['currency'] }} {{ number_format((float) $receipt['transaction']->balance_due, 0) }}</strong>
                                </div>
                            @endif

                            @if (in_array($receipt['transaction']->settlement_status, ['credit', 'layaway']))
                                <div class="dreams-receipt-center">
                                    <p style="margin:8px 0 0;font-size:11px;color:#92400e;">
                                        {{ $receipt['transaction']->settlement_status === 'credit' ? __('pos.credit_note') : __('pos.layaway_note') }}
                                    </p>
                                </div>
                            @endif

                            <hr>

                            <div class="dreams-receipt-footer dreams-receipt-center">
                                <p>@lang('pos.thank_you')</p>
                                <p>@lang('pos.computer_generated')</p>
                            </div>
                        </div>
                    </div>
                    <div class="dreams-receipt-actions">
                        <button type="button" class="dreams-action-btn clear" wire:click="closeReceiptModal">@lang('pos.done')</button>
                        <button type="button" class="dreams-action-btn pay" onclick="printPosReceipt()">@lang('pos.print_receipt')</button>
                    </div>
                </div>
            </div>
        @endif

        @if ($showCashModal)
            <div class="dreams-modal-overlay" wire:click.self="$set('showCashModal', false)">
                <div class="dreams-modal" wire:key="cash-modal">
                    <div class="dreams-modal-head">
                        <h3>{{ $cashMovementType === 'cash_in' ? __('pos.cash_in') : __('pos.cash_out') }}</h3>
                        <button type="button" class="dreams-modal-close" wire:click="$set('showCashModal', false)">&times;</button>
                    </div>
                    <div class="dreams-modal-body">
                        <div style="display:flex;flex-direction:column;gap:14px;">
                            <div class="dreams-modal-field" style="margin:0;">
                                <label>@lang('pos.amount_label', ['currency' => $currency])</label>
                                <input type="number" min="0" step="1" wire:model="cashAmount" class="dreams-number-input" placeholder="@lang('pos.enter_amount')">
                            </div>
                            <div class="dreams-modal-field" style="margin:0;">
                                <label>@lang('pos.reason')</label>
                                <textarea wire:model="cashReason" class="dreams-select" rows="2" placeholder="@lang('pos.optional_reason')" style="resize:vertical;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;justify-content:flex-end;padding:14px 18px;border-top:1px solid #e2e8f0;">
                        <button type="button" class="dreams-action-btn clear" wire:click="$set('showCashModal', false)">@lang('pos.cancel')</button>
                        <button type="button" class="dreams-action-btn {{ $cashMovementType === 'cash_in' ? 'pay' : 'void' }}" wire:click="saveCashMovement" @disabled(empty($cashAmount))>{{ $cashMovementType === 'cash_in' ? __('pos.record_cash_in') : __('pos.record_cash_out') }}</button>
                    </div>
                </div>
            </div>
        @endif

        @if ($showReportModal)
            @php $rd = $this->reportData(); @endphp
            <div class="dreams-modal-overlay" wire:click.self="$set('showReportModal', false)">
                <div class="dreams-modal" wire:key="report-modal" style="max-width:520px;">
                    <div class="dreams-modal-head">
                        <h3>{{ $reportType === 'x' ? __('pos.x_report_title') : __('pos.z_report_title') }} — {{ now()->format('d M Y') }}</h3>
                        <button type="button" class="dreams-modal-close" wire:click="$set('showReportModal', false)">&times;</button>
                    </div>
                    <div class="dreams-modal-body" style="background:#f8fafc;">
                        <div style="display:flex;flex-direction:column;gap:12px;">

                            {{-- Header --}}
                            <div style="text-align:center;margin-bottom:4px;">
                                <div style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:1px;">{{ $reportType === 'x' ? __('pos.x_report_sub') : __('pos.z_report_sub') }}</div>
                            </div>

                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.opened_at')</span><strong style="color:#0f172a;">{{ $rd['opened_at'] ? \Carbon\Carbon::parse($rd['opened_at'])->format('d M Y, H:i') : '—' }}</strong></div>

                            {{-- Sales Summary --}}
                            <div style="background:#e0f2fe;border-radius:8px;padding:10px 14px;font-size:12px;font-weight:700;color:#0369a1;text-transform:uppercase;letter-spacing:0.5px;">@lang('pos.sales_summary')</div>

                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.total_transactions')</span><strong style="color:#0f172a;">{{ $rd['count'] }}</strong></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.items_sold')</span><strong style="color:#0f172a;">{{ number_format($rd['items']) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.gross_sales')</span><strong style="color:#059669;">{{ $rd['currency'] }} {{ number_format($rd['grossSales'], 0) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.discounts')</span><strong style="color:#0f172a;">{{ $rd['currency'] }} {{ number_format($rd['totalDiscounts'], 0) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.refunds')</span><strong style="color:#dc2626;">{{ $rd['currency'] }} {{ number_format($rd['totalRefunds'], 0) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.taxes')</span><strong style="color:#0f172a;">{{ $rd['currency'] }} {{ number_format($rd['totalTaxes'], 0) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:700;color:#0f172a;padding:6px 10px;background:#f0fdf4;border-radius:6px;border:1px solid #bbf7d0;"><span>@lang('pos.net_sales')</span><strong style="color:#059669;">{{ $rd['currency'] }} {{ number_format($rd['netSales'], 0) }}</strong></div>

                            {{-- Payment Breakdown --}}
                            <div style="background:#e0f2fe;border-radius:8px;padding:10px 14px;font-size:12px;font-weight:700;color:#0369a1;text-transform:uppercase;letter-spacing:0.5px;">@lang('pos.payment_breakdown')</div>

                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.cash_label')</span><strong style="color:#059669;">{{ $rd['currency'] }} {{ number_format($rd['cashPayments'], 0) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.card')</span><strong style="color:#0f172a;">{{ $rd['currency'] }} {{ number_format($rd['cardPayments'], 0) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.mobile_money')</span><strong style="color:#0f172a;">{{ $rd['currency'] }} {{ number_format($rd['mobilePayments'], 0) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.bank_transfer')</span><strong style="color:#0f172a;">{{ $rd['currency'] }} {{ number_format($rd['bankPayments'], 0) }}</strong></div>

                            {{-- Cash Drawer --}}
                            <div style="background:#e0f2fe;border-radius:8px;padding:10px 14px;font-size:12px;font-weight:700;color:#0369a1;text-transform:uppercase;letter-spacing:0.5px;">@lang('pos.cash_drawer')</div>

                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.starting_cash')</span><strong style="color:#0f172a;">{{ $rd['currency'] }} {{ number_format($rd['startingCash'], 0) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.cash_in')</span><strong style="color:#059669;">{{ $rd['currency'] }} {{ number_format($rd['cashIn'], 0) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.cash_out')</span><strong style="color:#dc2626;">{{ $rd['currency'] }} {{ number_format($rd['cashOut'], 0) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.cash_sales')</span><strong style="color:#059669;">{{ $rd['currency'] }} {{ number_format($rd['cashSales'], 0) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;"><span>@lang('pos.cash_refunds')</span><strong style="color:#dc2626;">{{ $rd['currency'] }} {{ number_format($rd['cashRefunds'], 0) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:700;color:#0f172a;padding:6px 10px;background:#f0fdf4;border-radius:6px;border:1px solid #bbf7d0;"><span>@lang('pos.expected_cash')</span><strong style="color:#059669;">{{ $rd['currency'] }} {{ number_format($rd['expectedCash'], 0) }}</strong></div>

                            @if ($reportType === 'z')
                                {{-- Z Report Closing --}}
                                <div style="background:#fef3c7;border-radius:8px;padding:10px 14px;font-size:12px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:0.5px;">@lang('pos.close_session')</div>

                                <div>
                                    <label style="font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;display:block;">@lang('pos.actual_cash')</label>
                                    <div style="position:relative;">
                                        <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:14px;color:#94a3b8;">{{ $rd['currency'] }}</span>
                                        <input type="number" wire:model.live="zActualCash" min="0" step="1" placeholder="@lang('pos.enter_actual_cash')" style="width:100%;padding:10px 10px 10px 70px;border:1px solid #e2e8f0;border-radius:8px;font-size:15px;background:#fff;" />
                                    </div>
                                </div>

                                @if ($shortage < 0)
                                    <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;padding:6px 10px;background:#fef2f2;border-radius:6px;border:1px solid #fecaca;"><span>@lang('pos.shortage')</span><strong style="color:#dc2626;">{{ $rd['currency'] }} {{ number_format(abs($shortage), 0) }}</strong></div>
                                @elseif ($shortage > 0)
                                    <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;padding:10px 14px;background:#fff7ed;border-radius:6px;border:1px solid #fed7aa;"><span style="font-weight:600;">@lang('pos.overage')</span><strong style="color:#ea580c;font-size:14px;">{{ $rd['currency'] }} {{ number_format($shortage, 0) }}</strong></div>
                                @else
                                    <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;padding:10px 14px;background:#eff6ff;border-radius:6px;border:1px solid #bfdbfe;"><span style="font-weight:600;">@lang('pos.balanced')</span><strong style="color:#2563eb;font-size:14px;">{{ $rd['currency'] }} 0</strong></div>
                                @endif

                                <div>
                                    <label style="font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;display:block;">@lang('pos.closing_note') <span style="font-weight:400;color:#94a3b8;">@lang('pos.optional')</span></label>
                                    <textarea wire:model="zNote" rows="2" placeholder="@lang('pos.session_notes')" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;background:#fff;resize:none;"></textarea>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div style="display:flex;gap:18px;justify-content:flex-end;padding:18px 22px;border-top:1px solid #e2e8f0;">
                        <button type="button" class="dreams-action-btn clear" wire:click="$set('showReportModal', false)">{{ $reportType === 'z' ? __('pos.cancel') : __('pos.close') }}</button>
                        @if ($reportType === 'x')
                            <button type="button" class="dreams-action-btn pay" onclick="window.print()">@lang('pos.print')</button>
                            <button type="button" class="dreams-action-btn clear" style="background:#3b82f6;color:#fff;" onclick="window.print()">@lang('pos.export')</button>
                        @else
                            <button type="button" class="dreams-action-btn void" wire:click="generateZReport" onclick="return confirm('{{ __('pos.close_confirm') }}');">@lang('pos.close_z_report')</button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
    <script>
        function printPosReceipt() {
            var body = document.querySelector('.dreams-receipt-printable');
            if (!body) return;
            var w = window.open('', '_blank', 'width=420,height=600');
            if (!w) return;
            w.document.write('<html><head><title>Receipt</title><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Courier New,monospace;padding:16px;font-size:12px;color:#000}</style></head><body>' + body.outerHTML + '</body></html>');
            w.document.close();
            w.focus();
            w.print();
        }
    </script>
</x-filament-panels::page>
