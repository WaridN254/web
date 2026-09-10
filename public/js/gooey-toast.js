/**
 * gooey-toast – framework-agnostic build for Livewire
 * Source: janusfil/gooey-toast (MIT License)
 * Adapted to IIFE with Livewire event integration
 */
(function () {
  "use strict";

  /* ── internal.js ──────────────────────────────────────── */
  var DEFAULT_DURATION = 6000;
  var AUTO_EXPAND_DELAY = 150;
  var AUTO_COLLAPSE_DELAY = 4000;

  function normalizeDuration(value) {
    return value === undefined ? DEFAULT_DURATION : value;
  }

  function clamp(value, min, max) {
    return Math.min(max, Math.max(min, value));
  }

  function resolvePlacement(position) {
    return {
      align: position.endsWith("left") ? "left" : position.endsWith("center") ? "center" : "right",
      edge: position.startsWith("top") ? "top" : "bottom",
    };
  }

  function resolveAutopilot(options, duration) {
    if (options.autopilot === false || duration == null || duration <= 0) return {};
    var cfg = typeof options.autopilot === "object" ? options.autopilot : undefined;
    return {
      autoExpandDelayMs: clamp(cfg && cfg.expand != null ? cfg.expand : AUTO_EXPAND_DELAY, 0, duration),
      autoCollapseDelayMs: clamp(cfg && cfg.collapse != null ? cfg.collapse : AUTO_COLLAPSE_DELAY, 0, duration),
    };
  }

  /* ── icons.js ─────────────────────────────────────────── */
  var SVG_NS = "http://www.w3.org/2000/svg";

  function createSvgNode(tag) {
    return document.createElementNS(SVG_NS, tag);
  }

  function setAttrs(el, attrs) {
    for (var key in attrs) {
      if (attrs.hasOwnProperty(key)) el.setAttribute(key, attrs[key]);
    }
  }

  function createIcon(title) {
    var svg = createSvgNode("svg");
    setAttrs(svg, {
      xmlns: SVG_NS, width: "16", height: "16", viewBox: "0 0 24 24",
      fill: "none", stroke: "currentColor", "stroke-width": "2",
      "stroke-linecap": "round", "stroke-linejoin": "round",
    });
    var t = createSvgNode("title");
    t.textContent = title;
    svg.append(t);
    return svg;
  }

  function appendPath(svg, d) {
    var p = createSvgNode("path");
    p.setAttribute("d", d);
    svg.append(p);
  }

  function appendCircle(svg, cx, cy, r) {
    var c = createSvgNode("circle");
    setAttrs(c, { cx: cx, cy: cy, r: r });
    svg.append(c);
  }

  function appendLine(svg, x1, x2, y1, y2) {
    var l = createSvgNode("line");
    setAttrs(l, { x1: x1, x2: x2, y1: y1, y2: y2 });
    svg.append(l);
  }

  function createCheck() {
    var svg = createIcon("Check");
    appendPath(svg, "M20 6 9 17l-5-5");
    return svg;
  }

  function createX() {
    var svg = createIcon("X");
    appendPath(svg, "M18 6 6 18");
    appendPath(svg, "m6 6 12 12");
    return svg;
  }

  function createLoaderCircle() {
    var svg = createIcon("Loader Circle");
    svg.setAttribute("data-gooey-icon", "spin");
    svg.setAttribute("aria-hidden", "true");
    appendPath(svg, "M21 12a9 9 0 1 1-6.219-8.56");
    return svg;
  }

  function createCircleAlert() {
    var svg = createIcon("Circle Alert");
    appendCircle(svg, "12", "12", "10");
    appendLine(svg, "12", "12", "8", "12");
    appendLine(svg, "12", "12.01", "16", "16");
    return svg;
  }

  function createLifeBuoy() {
    var svg = createIcon("Life Buoy");
    appendCircle(svg, "12", "12", "10");
    appendPath(svg, "m4.93 4.93 4.24 4.24");
    appendPath(svg, "m14.83 9.17 4.24-4.24");
    appendPath(svg, "m14.83 14.83 4.24 4.24");
    appendPath(svg, "m9.17 14.83-4.24 4.24");
    appendCircle(svg, "12", "12", "4");
    return svg;
  }

  function createArrowRight() {
    var svg = createIcon("Arrow Right");
    appendPath(svg, "M5 12h14");
    appendPath(svg, "m12 5 7 7-7 7");
    return svg;
  }

  function createStateIcon(state) {
    switch (state) {
      case "success": return createCheck();
      case "loading": return createLoaderCircle();
      case "error": return createX();
      case "warning": return createCircleAlert();
      case "info": return createLifeBuoy();
      case "action": return createArrowRight();
      default: return createCheck();
    }
  }

  /* ── toast.js core ────────────────────────────────────── */
  var EXIT_DURATION = 260;
  var SWIPE_DISMISS_DISTANCE = 30;
  var SWIPE_MAX_TRANSLATE = 20;
  var HOVER_RESUME_DELAY = 50;
  var TOAST_FALLBACK_WIDTH = 350;
  var TOAST_HEIGHT = 44;
  var DEFAULT_ROUNDNESS = 18;
  var BLUR_RATIO = 0.5;
  var GOOEY_JOIN = 10;
  var TOAST_POSITIONS = [
    "top-left", "top-center", "top-right",
    "bottom-left", "bottom-center", "bottom-right",
  ];

  var store = {
    toasts: [],
    listeners: new Set(),
    position: "top-right",
    options: undefined,
    emit: function () {
      var _this = this;
      this.listeners.forEach(function (listener) { listener(_this.toasts); });
    },
    update: function (updater) {
      this.toasts = updater(this.toasts);
      this.emit();
    },
  };

  function isBrowser() {
    return typeof window !== "undefined" && typeof document !== "undefined";
  }

  var idCounter = 0;
  function generateId() {
    return ++idCounter + "-" + Date.now().toString(36) + "-" + Math.random().toString(36).slice(2, 8);
  }

  function isTimedDuration(value) {
    return value != null && value > 0;
  }

  function getNow() {
    return typeof performance !== "undefined" && typeof performance.now === "function"
      ? performance.now()
      : Date.now();
  }

  function timeoutKey(item) {
    return item.id + ":" + item.instanceId;
  }

  function mergeOptions(options) {
    var base = store.options || {};
    return Object.assign({}, base, options, {
      styles: Object.assign({}, base.styles || {}, options.styles || {}),
    });
  }

  function buildToastRecord(merged, id, fallbackPosition) {
    var duration = normalizeDuration(merged.duration);
    var placement = merged.position || fallbackPosition || store.position;
    return Object.assign({}, merged, {
      id: id,
      instanceId: generateId(),
      exiting: false,
      duration: duration,
      position: placement,
    }, resolveAutopilot(merged, duration));
  }

  function createToast(options) {
    var merged = mergeOptions(options);
    var id = merged.id || generateId();
    var existing = store.toasts.find(function (item) { return item.id === id && !item.exiting; });
    var next = buildToastRecord(merged, id, existing && existing.position);
    if (existing) {
      store.update(function (all) { return all.map(function (item) { return item.id === id ? next : item; }); });
    } else {
      store.update(function (all) { return all.filter(function (item) { return item.id !== id; }).concat([next]); });
    }
    return { id: id };
  }

  function updateToast(id, options) {
    var existing = store.toasts.find(function (item) { return item.id === id; });
    if (!existing) return;
    var merged = mergeOptions(Object.assign({}, options, { id: id }));
    var next = buildToastRecord(merged, id, existing.position);
    store.update(function (all) { return all.map(function (item) { return item.id === id ? next : item; }); });
  }

  var exitTimers = {};

  function dismissToast(id) {
    var existing = store.toasts.find(function (item) { return item.id === id; });
    if (!existing || existing.exiting) return;
    var key = timeoutKey(existing);
    store.update(function (all) {
      return all.map(function (item) {
        return item.id === id ? Object.assign({}, item, { exiting: true }) : item;
      });
    });
    var prev = exitTimers[key];
    if (prev && prev.remove != null) clearTimeout(prev.remove);
    var timers = {};
    timers.remove = window.setTimeout(function () {
      delete exitTimers[key];
      store.update(function (all) {
        return all.filter(function (item) {
          return !(item.id === existing.id && item.instanceId === existing.instanceId);
        });
      });
    }, EXIT_DURATION);
    exitTimers[key] = timers;
  }

  function resolveRenderableValue(input) {
    var value = input;
    while (typeof value === "function") value = value();
    return value;
  }

  function isNode(value) {
    return typeof Node !== "undefined" && value instanceof Node;
  }

  function renderRenderable(container, value) {
    var resolved = resolveRenderableValue(value);
    if (resolved == null) return false;
    if (typeof resolved === "string" || typeof resolved === "number") {
      var text = String(resolved);
      if (!text.trim()) return false;
      container.append(document.createTextNode(text));
      return true;
    }
    if (isNode(resolved)) {
      container.append(resolved.cloneNode(true));
      return true;
    }
    return false;
  }

  function renderIcon(value, state) {
    var resolved = resolveRenderableValue(value);
    if (resolved == null) return createStateIcon(state);
    if (typeof resolved === "string" || typeof resolved === "number") return document.createTextNode(String(resolved));
    if (isNode(resolved)) return resolved.cloneNode(true);
    return createStateIcon(state);
  }

  /* ── ToastView class ─────────────────────────────────── */
  function ToastView(item, placement, callbacks) {
    var _this = this;
    this.sizeObserver = null;
    this.readyRaf = null;
    this.autoExpandTimer = null;
    this.autoCollapseTimer = null;
    this.pointerStartY = null;
    this.hasContent = false;
    this.expanded = false;
    this.containerWidth = TOAST_FALLBACK_WIDTH;
    this.headerWidth = TOAST_HEIGHT;
    this.contentHeight = 0;

    this.handleMouseEnter = function () {
      _this.callbacks.onEnter(_this.id);
      _this.clearAutoPilotTimers();
      if (_this.canExpand()) _this.setExpanded(true);
    };

    this.handleMouseLeave = function () {
      _this.callbacks.onLeave(_this.id);
      _this.setExpanded(false);
    };

    this.handlePointerDown = function (event) {
      if (_this.currentItem.exiting) return;
      if (event.pointerType === "mouse" && event.button !== 0) return;
      var target = event.target;
      if (target && target.closest && target.closest("[data-gooey-button]")) return;
      _this.pointerStartY = event.clientY;
      _this.root.setPointerCapture(event.pointerId);
    };

    this.handlePointerMove = function (event) {
      if (_this.pointerStartY == null) return;
      var delta = event.clientY - _this.pointerStartY;
      var sign = delta < 0 ? -1 : 1;
      var clamped = Math.min(Math.abs(delta), SWIPE_MAX_TRANSLATE) * sign;
      _this.root.style.setProperty("--gooey-drag-y", clamped + "px");
    };

    this.handlePointerUp = function (event) {
      if (_this.pointerStartY == null) return;
      var delta = event.clientY - _this.pointerStartY;
      _this.resetPointerState(event.pointerId);
      if (Math.abs(delta) >= SWIPE_DISMISS_DISTANCE) {
        _this.callbacks.onDismiss(_this.id);
      }
    };

    this.handlePointerCancel = function (event) {
      if (_this.pointerStartY == null) return;
      _this.resetPointerState(event.pointerId);
    };

    this.id = item.id;
    this.currentItem = item;
    this.placement = placement;
    this.callbacks = callbacks;

    /* root button */
    this.root = document.createElement("button");
    this.root.type = "button";
    this.root.setAttribute("data-gooey-toast", "");
    this.root.dataset.ready = "false";
    this.root.dataset.expanded = "false";
    this.root.dataset.exiting = String(Boolean(item.exiting));

    /* SVG canvas */
    this.canvasEl = document.createElement("div");
    this.canvasEl.setAttribute("data-gooey-canvas", "");
    this.svgEl = document.createElementNS(SVG_NS, "svg");
    this.svgEl.setAttribute("data-gooey-svg", "");
    this.svgEl.setAttribute("width", String(TOAST_FALLBACK_WIDTH));
    this.svgEl.setAttribute("height", String(TOAST_HEIGHT));
    this.svgEl.setAttribute("viewBox", "0 0 " + TOAST_FALLBACK_WIDTH + " " + TOAST_HEIGHT);

    var defs = document.createElementNS(SVG_NS, "defs");
    var filter = document.createElementNS(SVG_NS, "filter");
    var filterId = "gooey-toast-" + item.id + "-" + item.instanceId;
    filter.setAttribute("id", filterId);
    filter.setAttribute("x", "-20%");
    filter.setAttribute("y", "-20%");
    filter.setAttribute("width", "140%");
    filter.setAttribute("height", "140%");
    filter.setAttribute("color-interpolation-filters", "sRGB");

    this.blurNode = document.createElementNS(SVG_NS, "feGaussianBlur");
    this.blurNode.setAttribute("in", "SourceGraphic");
    this.blurNode.setAttribute("stdDeviation", String(DEFAULT_ROUNDNESS * BLUR_RATIO));
    this.blurNode.setAttribute("result", "blur");

    var colorMatrix = document.createElementNS(SVG_NS, "feColorMatrix");
    colorMatrix.setAttribute("in", "blur");
    colorMatrix.setAttribute("mode", "matrix");
    colorMatrix.setAttribute("values", "1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 20 -10");
    colorMatrix.setAttribute("result", "goo");

    var composite = document.createElementNS(SVG_NS, "feComposite");
    composite.setAttribute("in", "SourceGraphic");
    composite.setAttribute("in2", "goo");
    composite.setAttribute("operator", "atop");

    filter.append(this.blurNode, colorMatrix, composite);
    defs.append(filter);

    var group = document.createElementNS(SVG_NS, "g");
    group.setAttribute("filter", "url(#" + filterId + ")");

    this.pillRect = document.createElementNS(SVG_NS, "rect");
    this.pillRect.setAttribute("data-gooey-pill", "");
    this.bodyRect = document.createElementNS(SVG_NS, "rect");
    this.bodyRect.setAttribute("data-gooey-body", "");
    group.append(this.pillRect, this.bodyRect);
    this.svgEl.append(defs, group);
    this.canvasEl.append(this.svgEl);

    /* header */
    this.headerEl = document.createElement("div");
    this.headerEl.setAttribute("data-gooey-header", "");
    this.badgeEl = document.createElement("div");
    this.badgeEl.setAttribute("data-gooey-badge", "");
    this.titleEl = document.createElement("span");
    this.titleEl.setAttribute("data-gooey-title", "");
    this.titleMeasureEl = document.createElement("span");
    this.titleMeasureEl.setAttribute("data-gooey-title", "");
    this.titleMeasureEl.setAttribute("data-gooey-title-measure", "");

    this.timeoutTrackEl = document.createElement("span");
    this.timeoutTrackEl.setAttribute("data-gooey-time-track", "");
    this.timeoutTrackEl.hidden = true;
    this.timeoutFillEl = document.createElement("span");
    this.timeoutFillEl.setAttribute("data-gooey-time-fill", "");
    this.timeoutTrackEl.append(this.timeoutFillEl);
    this.headerEl.append(this.badgeEl, this.titleEl, this.timeoutTrackEl);

    /* content */
    this.contentEl = document.createElement("div");
    this.contentEl.setAttribute("data-gooey-content", "");
    this.contentEl.dataset.visible = "false";
    this.descriptionEl = document.createElement("div");
    this.descriptionEl.setAttribute("data-gooey-description", "");
    this.contentEl.append(this.descriptionEl);

    this.root.append(this.canvasEl, this.headerEl, this.contentEl, this.titleMeasureEl);

    this.root.addEventListener("mouseenter", this.handleMouseEnter);
    this.root.addEventListener("mouseleave", this.handleMouseLeave);
    this.root.addEventListener("pointerdown", this.handlePointerDown);
    this.root.addEventListener("pointermove", this.handlePointerMove, { passive: true });
    this.root.addEventListener("pointerup", this.handlePointerUp, { passive: true });
    this.root.addEventListener("pointercancel", this.handlePointerCancel, { passive: true });

    if (typeof ResizeObserver !== "undefined") {
      this.sizeObserver = new ResizeObserver(function () { _this.syncMetrics(); });
      this.sizeObserver.observe(this.root);
      this.sizeObserver.observe(this.headerEl);
      this.sizeObserver.observe(this.descriptionEl);
    }

    this.update(item, placement);
    this.readyRaf = requestAnimationFrame(function () {
      _this.root.dataset.ready = "true";
      _this.readyRaf = null;
      _this.syncMetrics();
    });
  }

  ToastView.prototype.update = function (item, placement) {
    this.currentItem = item;
    this.applyPlacement(placement);
    this.root.dataset.exiting = String(Boolean(item.exiting));
    this.render(item);
    if (!item.exiting && !this.canExpand()) this.setExpanded(false);
    if (item.exiting) this.clearAutoPilotTimers();
    else this.refreshAutopilot();
  };

  ToastView.prototype.destroy = function () {
    if (this.readyRaf != null) { cancelAnimationFrame(this.readyRaf); this.readyRaf = null; }
    if (this.sizeObserver) { this.sizeObserver.disconnect(); this.sizeObserver = null; }
    this.clearAutoPilotTimers();
    this.pointerStartY = null;
    this.root.style.removeProperty("--gooey-drag-y");
    this.root.removeEventListener("mouseenter", this.handleMouseEnter);
    this.root.removeEventListener("mouseleave", this.handleMouseLeave);
    this.root.removeEventListener("pointerdown", this.handlePointerDown);
    this.root.removeEventListener("pointermove", this.handlePointerMove);
    this.root.removeEventListener("pointerup", this.handlePointerUp);
    this.root.removeEventListener("pointercancel", this.handlePointerCancel);
    this.root.remove();
  };

  ToastView.prototype.applyPlacement = function (placement) {
    this.placement = placement;
    this.root.dataset.position = placement.align;
    this.root.dataset.edge = placement.edge;
    this.applyGeometry();
  };

  ToastView.prototype.render = function (item) {
    var state = item.state || "success";
    var title = item.title || state;
    var showTimeoutIndicator = Boolean(item.timeoutIndicator) && isTimedDuration(item.duration) && !item.exiting;

    this.root.dataset.state = state;
    if (item.fill) this.root.style.setProperty("--gooey-fill", item.fill);
    else this.root.style.removeProperty("--gooey-fill");

    if (item.roundness != null) this.root.style.setProperty("--gooey-radius", Math.max(0, item.roundness) + "px");
    else this.root.style.removeProperty("--gooey-radius");

    this.badgeEl.dataset.state = state;
    this.badgeEl.className = (item.styles && item.styles.badge) || "";
    this.badgeEl.replaceChildren(renderIcon(item.icon, state));

    this.titleEl.dataset.state = state;
    this.titleEl.className = (item.styles && item.styles.title) || "";
    this.titleEl.textContent = title;

    this.titleMeasureEl.dataset.state = state;
    this.titleMeasureEl.className = (item.styles && item.styles.title) || "";
    this.titleMeasureEl.textContent = title;

    this.timeoutTrackEl.dataset.state = state;
    this.timeoutFillEl.dataset.state = state;
    this.setTimeoutIndicator(showTimeoutIndicator, 1, false);

    this.descriptionEl.className = (item.styles && item.styles.description) || "";
    this.descriptionEl.replaceChildren();

    var hasContent = renderRenderable(this.descriptionEl, item.description);

    if (item.button) {
      this.descriptionEl.append(this.buildActionButton(item.button, state, item.styles && item.styles.button));
      hasContent = true;
    }

    this.hasContent = hasContent;
    this.contentEl.style.display = hasContent ? "" : "none";

    if (!hasContent) {
      this.expanded = false;
      this.root.dataset.expanded = "false";
      this.contentEl.dataset.visible = "false";
    }
    this.syncMetrics();
  };

  ToastView.prototype.buildActionButton = function (button, state, className) {
    var action = document.createElement("button");
    action.type = "button";
    action.setAttribute("data-gooey-button", "");
    action.dataset.state = state;
    action.className = className || "";
    action.textContent = button.title;
    action.addEventListener("click", function (event) {
      event.preventDefault();
      event.stopPropagation();
      button.onClick();
    });
    return action;
  };

  ToastView.prototype.setTimeoutIndicator = function (visible, progress, paused) {
    this.root.dataset.timeoutIndicator = String(visible);
    this.root.dataset.timeoutPaused = String(paused);
    this.timeoutTrackEl.hidden = !visible;
    this.timeoutFillEl.style.transform = "scaleX(" + clamp(progress, 0, 1) + ")";
  };

  ToastView.prototype.hasExpandableContent = function () {
    return this.hasContent && (this.currentItem.state || "success") !== "loading";
  };

  ToastView.prototype.canExpand = function () {
    return this.hasExpandableContent() && !this.currentItem.exiting;
  };

  ToastView.prototype.setExpanded = function (next) {
    var resolved = next && this.hasContent;
    if (this.expanded === resolved) return;
    this.expanded = resolved;
    this.root.dataset.expanded = String(resolved);
    this.contentEl.dataset.visible = String(resolved);
    this.applyGeometry();
  };

  ToastView.prototype.syncMetrics = function () {
    var width = this.root.getBoundingClientRect().width;
    if (width > 0) this.containerWidth = Math.max(1, Math.round(width));
    var nextHeaderWidth = this.measureHeaderWidth();
    if (nextHeaderWidth > 0) this.headerWidth = clamp(nextHeaderWidth, TOAST_HEIGHT, this.containerWidth);
    this.contentHeight = this.hasContent ? Math.max(0, Math.ceil(this.descriptionEl.scrollHeight)) : 0;
    this.applyGeometry();
  };

  ToastView.prototype.measureHeaderWidth = function () {
    var styles = window.getComputedStyle(this.headerEl);
    var gapRaw = styles.columnGap && styles.columnGap !== "normal" ? styles.columnGap : styles.gap;
    var gap = Number.parseFloat(gapRaw || "0") || 0;
    var paddingLeft = Number.parseFloat(styles.paddingLeft || "0") || 0;
    var paddingRight = Number.parseFloat(styles.paddingRight || "0") || 0;
    var badgeWidth = this.badgeEl.getBoundingClientRect().width;
    var titleWidth = Math.max(this.titleMeasureEl.getBoundingClientRect().width, this.titleEl.scrollWidth);
    return Math.ceil(badgeWidth + titleWidth + gap + paddingLeft + paddingRight + 2);
  };

  ToastView.prototype.alignedX = function (width) {
    if (this.placement.align === "right") return this.containerWidth - width;
    if (this.placement.align === "center") return (this.containerWidth - width) / 2;
    return 0;
  };

  ToastView.prototype.applyGeometry = function () {
    var canOpen = this.expanded && this.hasExpandableContent();
    var visibleContentHeight = canOpen ? this.contentHeight : 0;
    var visualHeight = TOAST_HEIGHT + visibleContentHeight;
    var headerWidth = clamp(this.headerWidth, TOAST_HEIGHT, this.containerWidth);
    var bodyWidth = this.hasContent ? this.containerWidth : headerWidth;
    var headerX = this.alignedX(headerWidth);
    var bodyX = this.alignedX(bodyWidth);
    var isTopEdge = this.placement.edge === "top";
    var bodyHeight = canOpen ? visibleContentHeight + GOOEY_JOIN : 0;
    var pillY = isTopEdge ? 0 : visualHeight - TOAST_HEIGHT;
    var bodyY = isTopEdge ? TOAST_HEIGHT - GOOEY_JOIN : 0;
    var roundness = Math.max(0, this.currentItem.roundness != null ? this.currentItem.roundness : DEFAULT_ROUNDNESS);
    var blur = roundness * BLUR_RATIO;
    var fill = this.currentItem.fill || "#FFFFFF";

    this.root.style.setProperty("--_h", visualHeight + "px");
    this.root.style.setProperty("--_hx", headerX + "px");
    this.root.style.setProperty("--_hw", headerWidth + "px");
    this.root.style.setProperty("--_bx", bodyX + "px");
    this.root.style.setProperty("--_bw", bodyWidth + "px");
    this.contentEl.dataset.visible = String(canOpen);
    this.svgEl.setAttribute("width", String(this.containerWidth));
    this.svgEl.setAttribute("height", String(visualHeight));
    this.svgEl.setAttribute("viewBox", "0 0 " + this.containerWidth + " " + visualHeight);
    this.blurNode.setAttribute("stdDeviation", String(blur));
    this.pillRect.setAttribute("x", String(headerX));
    this.pillRect.setAttribute("y", String(pillY));
    this.pillRect.setAttribute("width", String(headerWidth));
    this.pillRect.setAttribute("height", String(TOAST_HEIGHT));
    this.pillRect.setAttribute("rx", String(roundness));
    this.pillRect.setAttribute("ry", String(roundness));
    this.pillRect.setAttribute("fill", fill);
    this.bodyRect.setAttribute("x", String(bodyX));
    this.bodyRect.setAttribute("y", String(bodyY));
    this.bodyRect.setAttribute("width", String(bodyWidth));
    this.bodyRect.setAttribute("height", String(bodyHeight));
    this.bodyRect.setAttribute("rx", String(roundness));
    this.bodyRect.setAttribute("ry", String(roundness));
    this.bodyRect.setAttribute("fill", fill);
  };

  ToastView.prototype.refreshAutopilot = function () {
    this.clearAutoPilotTimers();
    if (!this.canExpand()) return;
    var expandDelay = this.currentItem.autoExpandDelayMs;
    var collapseDelay = this.currentItem.autoCollapseDelayMs;
    if (expandDelay == null && collapseDelay == null) return;
    var _this = this;
    if ((expandDelay != null ? expandDelay : 0) <= 0) {
      this.setExpanded(true);
    } else {
      this.autoExpandTimer = window.setTimeout(function () {
        _this.autoExpandTimer = null;
        _this.setExpanded(true);
      }, expandDelay);
    }
    if (collapseDelay != null) {
      this.autoCollapseTimer = window.setTimeout(function () {
        _this.autoCollapseTimer = null;
        _this.setExpanded(false);
      }, collapseDelay);
    }
  };

  ToastView.prototype.clearAutoPilotTimers = function () {
    if (this.autoExpandTimer != null) { clearTimeout(this.autoExpandTimer); this.autoExpandTimer = null; }
    if (this.autoCollapseTimer != null) { clearTimeout(this.autoCollapseTimer); this.autoCollapseTimer = null; }
  };

  ToastView.prototype.resetPointerState = function (pointerId) {
    this.pointerStartY = null;
    this.root.style.removeProperty("--gooey-drag-y");
    if (this.root.hasPointerCapture(pointerId)) this.root.releasePointerCapture(pointerId);
  };

  /* ── ToasterManager ───────────────────────────────────── */
  function ToasterManager(options) {
    options = options || {};
    this.hovering = false;
    this.hoverResumeTimer = null;
    this.viewports = {};
    this.views = {};
    this.dismissStates = {};
    this.indicatorRaf = null;
    this.mounted = true;
    this.target = options.target || document.body;
    this.position = options.position || store.position;
    this.offset = options.offset;
    this.defaultOptions = options.options;
    store.position = this.position;
    store.options = this.defaultOptions;

    var _this = this;
    this.listener = function (toasts) { _this.render(toasts); };
    store.listeners.add(this.listener);
    this.render(store.toasts);
  }

  ToasterManager.prototype.configure = function (options) {
    if (options && options.target && options.target !== this.target) {
      for (var pos in this.viewports) {
        this.target.append(this.viewports[pos]);
      }
      this.target = options.target;
    }
    if (options && options.position) this.position = options.position;
    if (options && options.offset !== undefined) this.offset = options.offset;
    if (options && options.options !== undefined) this.defaultOptions = options.options;
    store.position = this.position;
    store.options = this.defaultOptions;
    this.render(store.toasts);
  };

  ToasterManager.prototype.unmount = function () {
    if (!this.mounted) return;
    this.mounted = false;
    store.listeners.delete(this.listener);
    for (var key in this.dismissStates) {
      var state = this.dismissStates[key];
      if (state && state.timer != null) clearTimeout(state.timer);
    }
    this.dismissStates = {};
    if (this.indicatorRaf != null) { cancelAnimationFrame(this.indicatorRaf); this.indicatorRaf = null; }
    if (this.hoverResumeTimer != null) { clearTimeout(this.hoverResumeTimer); this.hoverResumeTimer = null; }
    for (var id in this.views) this.views[id].destroy();
    this.views = {};
    for (var pos in this.viewports) this.viewports[pos].remove();
    this.viewports = {};
  };

  ToasterManager.prototype.render = function (toasts) {
    var _this = this;
    if (!this.mounted) return;
    var toastIds = {};
    toasts.forEach(function (t) { toastIds[t.id] = true; });
    for (var vid in this.views) {
      if (!toastIds[vid]) { this.views[vid].destroy(); delete this.views[vid]; }
    }
    var byPosition = {};
    toasts.forEach(function (toast) {
      var pos = toast.position || _this.position;
      if (!byPosition[pos]) byPosition[pos] = [];
      byPosition[pos].push(toast);
    });
    TOAST_POSITIONS.forEach(function (position) {
      var items = byPosition[position] || [];
      if (!items.length) { _this.removeViewport(position); return; }
      var viewport = _this.ensureViewport(position);
      _this.applyViewportOffset(viewport, position);
      var placement = resolvePlacement(position);
      items.forEach(function (item) {
        var existing = _this.views[item.id];
        if (existing) { existing.update(item, placement); viewport.append(existing.root); return; }
        var view = new ToastView(item, placement, {
          onEnter: function () { _this.handleEnter(); },
          onLeave: function () { _this.handleLeave(); },
          onDismiss: function (id) { dismissToast(id); },
        });
        _this.views[item.id] = view;
        viewport.append(view.root);
      });
    });
    var dismissKeys = {};
    toasts.forEach(function (toast) {
      var duration = normalizeDuration(toast.duration);
      if (!toast.exiting && isTimedDuration(duration)) {
        var key = timeoutKey(toast);
        dismissKeys[key] = true;
        _this.ensureDismissState(toast, key, duration);
      }
    });
    for (var dk in this.dismissStates) {
      if (!dismissKeys[dk]) this.deleteDismissState(dk);
    }
    if (this.hovering && !this.isAnyToastHovered()) this.hovering = false;
    this.scheduleDismiss(toasts);
    this.syncTimeoutIndicators(toasts);
  };

  ToasterManager.prototype.ensureViewport = function (position) {
    if (this.viewports[position]) {
      this.viewports[position].dataset.position = position;
      return this.viewports[position];
    }
    var section = document.createElement("section");
    section.setAttribute("data-gooey-viewport", "");
    section.dataset.position = position;
    section.setAttribute("aria-live", "polite");
    this.target.append(section);
    this.viewports[position] = section;
    return section;
  };

  ToasterManager.prototype.removeViewport = function (position) {
    if (!this.viewports[position]) return;
    this.viewports[position].remove();
    delete this.viewports[position];
  };

  ToasterManager.prototype.applyViewportOffset = function (viewport, position) {
    if (this.offset === undefined) {
      viewport.style.top = ""; viewport.style.right = ""; viewport.style.bottom = ""; viewport.style.left = "";
      return;
    }
    var value = typeof this.offset === "object" ? this.offset : { top: this.offset, right: this.offset, bottom: this.offset, left: this.offset };
    var toCss = function (entry) { return typeof entry === "number" ? entry + "px" : entry; };
    viewport.style.top = position.startsWith("top") && value.top !== undefined ? toCss(value.top) : "";
    viewport.style.bottom = position.startsWith("bottom") && value.bottom !== undefined ? toCss(value.bottom) : "";
    viewport.style.left = position.endsWith("left") && value.left !== undefined ? toCss(value.left) : "";
    viewport.style.right = position.endsWith("right") && value.right !== undefined ? toCss(value.right) : "";
  };

  ToasterManager.prototype.ensureDismissState = function (toast, key, duration) {
    key = key || timeoutKey(toast);
    duration = duration || normalizeDuration(toast.duration);
    if (toast.exiting || !isTimedDuration(duration)) return null;
    if (this.dismissStates[key]) return this.dismissStates[key];
    var state = { timer: null, duration: duration, remaining: duration, startedAt: null };
    this.dismissStates[key] = state;
    return state;
  };

  ToasterManager.prototype.deleteDismissState = function (key) {
    var state = this.dismissStates[key];
    if (!state) return;
    if (state.timer != null) clearTimeout(state.timer);
    delete this.dismissStates[key];
  };

  ToasterManager.prototype.getRemainingMs = function (state, timestamp) {
    timestamp = timestamp || getNow();
    if (state.startedAt == null) return clamp(state.remaining, 0, state.duration);
    return clamp(state.remaining - (timestamp - state.startedAt), 0, state.duration);
  };

  ToasterManager.prototype.syncTimeoutIndicators = function (toasts) {
    if (this.indicatorRaf != null) { cancelAnimationFrame(this.indicatorRaf); this.indicatorRaf = null; }
    var _this = this;
    var currentTime = getNow();
    var needsRaf = false;
    toasts.forEach(function (toast) {
      var view = _this.views[toast.id];
      if (!view) return;
      var duration = normalizeDuration(toast.duration);
      var visible = Boolean(toast.timeoutIndicator) && !toast.exiting && isTimedDuration(duration);
      if (!visible) { view.setTimeoutIndicator(false, 1, false); return; }
      var state = _this.dismissStates[timeoutKey(toast)];
      var remaining = state ? _this.getRemainingMs(state, currentTime) : duration;
      var progress = remaining / duration;
      var paused = _this.hovering || (state && state.timer == null);
      view.setTimeoutIndicator(true, progress, paused);
      if (!paused && progress > 0) needsRaf = true;
    });
    if (needsRaf) {
      this.indicatorRaf = requestAnimationFrame(function () {
        _this.indicatorRaf = null;
        _this.syncTimeoutIndicators(store.toasts);
      });
    }
  };

  ToasterManager.prototype.scheduleDismiss = function (toasts) {
    var _this = this;
    if (this.hovering) return;
    toasts.forEach(function (toast) {
      if (toast.exiting) return;
      var duration = normalizeDuration(toast.duration);
      if (!isTimedDuration(duration)) return;
      var key = timeoutKey(toast);
      var state = _this.ensureDismissState(toast, key, duration);
      if (!state || state.timer != null) return;
      state.remaining = clamp(state.remaining, 0, state.duration);
      state.startedAt = getNow();
      state.timer = window.setTimeout(function () {
        _this.deleteDismissState(key);
        dismissToast(toast.id);
        _this.syncTimeoutIndicators(store.toasts);
      }, state.remaining);
    });
  };

  ToasterManager.prototype.pauseDismissTimers = function () {
    var _this = this;
    var currentTime = getNow();
    Object.keys(this.dismissStates).forEach(function (key) {
      var state = _this.dismissStates[key];
      if (state.timer == null) return;
      clearTimeout(state.timer);
      state.remaining = _this.getRemainingMs(state, currentTime);
      state.startedAt = null;
      state.timer = null;
    });
  };

  ToasterManager.prototype.handleEnter = function () {
    if (this.hoverResumeTimer != null) { clearTimeout(this.hoverResumeTimer); this.hoverResumeTimer = null; }
    if (!this.hovering) {
      this.hovering = true;
      this.pauseDismissTimers();
      this.syncTimeoutIndicators(store.toasts);
    }
  };

  ToasterManager.prototype.handleLeave = function () {
    var _this = this;
    if (this.hoverResumeTimer != null) clearTimeout(this.hoverResumeTimer);
    this.hoverResumeTimer = window.setTimeout(function () {
      _this.hoverResumeTimer = null;
      if (_this.isAnyToastHovered()) return;
      _this.hovering = false;
      _this.scheduleDismiss(store.toasts);
      _this.syncTimeoutIndicators(store.toasts);
    }, HOVER_RESUME_DELAY);
  };

  ToasterManager.prototype.isAnyToastHovered = function () {
    for (var id in this.views) {
      if (this.views[id].root.matches(":hover")) return true;
    }
    return false;
  };

  /* ── Singleton ────────────────────────────────────────── */
  var singletonManager = null;

  function ensureManager() {
    if (!isBrowser()) return null;
    if (!singletonManager) singletonManager = new ToasterManager();
    return singletonManager;
  }

  function createToaster(options) {
    var manager = ensureManager();
    if (!manager) return { update: function () {}, unmount: function () {} };
    manager.configure(options || {});
    return {
      update: function (next) { manager.configure(next); },
      unmount: function () {
        if (singletonManager === manager) singletonManager = null;
        manager.unmount();
      },
    };
  }

  function configureToaster(options) {
    var manager = ensureManager();
    if (manager) manager.configure(options || {});
  }

  function unmountToaster() {
    if (!singletonManager) return;
    singletonManager.unmount();
    singletonManager = null;
  }

  function showToast(opts, state) {
    if (!isBrowser()) return generateId();
    ensureManager();
    return createToast(state ? Object.assign({}, opts, { state: state }) : opts).id;
  }

  /* ── Public API ───────────────────────────────────────── */
  var toast = {
    show: function (opts) { return showToast(opts); },
    success: function (opts) { return showToast(opts, "success"); },
    error: function (opts) { return showToast(opts, "error"); },
    warning: function (opts) { return showToast(opts, "warning"); },
    info: function (opts) { return showToast(opts, "info"); },
    action: function (opts) { return showToast(opts, "action"); },
    promise: function (promise, opts) {
      if (!isBrowser()) return typeof promise === "function" ? promise() : promise;
      ensureManager();
      var id = createToast(Object.assign({}, opts.loading, { state: "loading", duration: null, position: opts.position })).id;
      var pending = typeof promise === "function" ? promise() : promise;
      pending.then(function (data) {
        if (opts.action) {
          var action = typeof opts.action === "function" ? opts.action(data) : opts.action;
          updateToast(id, Object.assign({}, action, { state: "action", id: id }));
          return;
        }
        var success = typeof opts.success === "function" ? opts.success(data) : opts.success;
        updateToast(id, Object.assign({}, success, { state: "success", id: id }));
      }).catch(function (error) {
        var failure = typeof opts.error === "function" ? opts.error(error) : opts.error;
        updateToast(id, Object.assign({}, failure, { state: "error", id: id }));
      });
      return pending;
    },
    dismiss: function (id) { if (isBrowser()) dismissToast(id); },
    clear: function (position) {
      if (!isBrowser()) return;
      if (position) {
        store.update(function (all) { return all.filter(function (item) { return item.position !== position; }); });
        return;
      }
      store.update(function () { return []; });
    },
    update: function (id, options) { if (isBrowser()) updateToast(id, options); },
  };

  /* ── Livewire integration ─────────────────────────────── */
  // Expose global HalisToast
  window.HalisToast = toast;

  function handleToastEvent(e) {
    var data = e.detail;
    if (Array.isArray(data)) data = data[0];
    if (!data) return;
    var method = data.type || 'show';
    if (typeof toast[method] === 'function') {
      var opts = { title: data.title || '' };
      if (data.description) opts.description = data.description;
      if (data.duration != null) opts.duration = data.duration;
      if (data.id) opts.id = data.id;
      if (data.button) {
        opts.button = {
          title: data.button.title || 'OK',
          onClick: function () {
            if (data.button.url) {
              window.location.href = data.button.url;
            } else if (data.button.event) {
              window.Livewire && window.Livewire.dispatch
                ? Livewire.dispatch(data.button.event, data.button.params || {})
                : window.dispatchEvent(new CustomEvent(data.button.event, { detail: data.button.params || {} }));
            } else if (data.button.js) {
              eval(data.button.js);
            }
          },
        };
      }
      toast[method](opts);
    }
  }

  // Listen on browser custom event (works with Livewire dispatch + Filament)
  document.addEventListener('show-gooey-toast', handleToastEvent);
  document.addEventListener('toast', handleToastEvent);

  // Initialize auto-mount
  if (isBrowser()) {
    createToaster({ position: "top-right", offset: { top: "80px", right: "20px", bottom: "1rem", left: "1rem" } });
  }

})();
