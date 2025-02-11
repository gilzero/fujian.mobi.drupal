/**
 * Generated by a build script. Do not modify.
 * Check orginal .jsx file.
 */
/* eslint-disable */

(function(Drupal2, wp2, drupalSettings2) {
  const { blockEditor, components, element } = wp2;
  const { useState, useEffect, useCallback } = element;
  const { InspectorControls } = blockEditor;
  const { SelectControl, Card, CardBody, Placeholder, Spinner } = components;
  const __ = Drupal2.t;
  function openBlockSettings(type, contentBlockId) {
    const entityId = drupalSettings2.gutenberg.entityId || null;
    const entityType = "node";
    const entityBundle = drupalSettings2.gutenberg.nodeType;
    const ajaxSettings = {
      url: Drupal2.url(`editor/content_block_type/settings/${type}/${contentBlockId}/${entityType}/${entityId}/${entityBundle}`),
      dialogType: "dialog",
      dialogRenderer: "sidebar"
    };
    Drupal2.ajax(ajaxSettings).execute();
  }
  function RenderedEntity({ id, viewMode = "default" }) {
    const renderedEntityRef = useCallback(async (node) => {
      if (node !== null) {
        node.id = `content_block-${id}-${viewMode}`;
        const element_settings = {
          url: Drupal2.url(`editor/content_block/render/${id}/${viewMode}`),
          progress: false
        };
        Drupal2.ajax(element_settings).execute();
      }
    }, [id, viewMode]);
    return /* @__PURE__ */ React.createElement("div", { ref: renderedEntityRef }, /* @__PURE__ */ React.createElement(Spinner, null));
  }
  function ContentBlock({ type, contentBlockId, name, viewMode: viewModeOriginal, onViewModeChange }) {
    const settingsFormRef = useCallback((node) => {
      if (node !== null) {
        openBlockSettings(type, contentBlockId);
      }
    }, [type, contentBlockId]);
    const [viewMode, setViewMode] = useState(viewModeOriginal);
    const [viewModeOptions, setViewModeOptions] = useState([]);
    useEffect(() => {
      if (onViewModeChange)
        onViewModeChange(viewMode);
    }, [viewMode, onViewModeChange]);
    useEffect(() => {
      const fetchViewModes = async () => {
        const response = await fetch(Drupal2.url(`editor/entity/view_modes/block_content/${type}`));
        const result = await response.json();
        setViewModeOptions(result.view_modes);
      };
      fetchViewModes();
    }, [type]);
    return /* @__PURE__ */ React.createElement("div", null, /* @__PURE__ */ React.createElement(InspectorControls, { key: "content-block-settings" }, /* @__PURE__ */ React.createElement(Card, null, /* @__PURE__ */ React.createElement(CardBody, null, /* @__PURE__ */ React.createElement(
      SelectControl,
      {
        label: __("View mode"),
        value: viewMode,
        options: Object.entries(viewModeOptions).map(([k, v]) => ({ label: v, value: k })),
        onChange: (newValue) => setViewMode(newValue),
        __nextHasNoMarginBottom: true
      }
    ))), /* @__PURE__ */ React.createElement(Card, null, /* @__PURE__ */ React.createElement(CardBody, null, /* @__PURE__ */ React.createElement("div", { ref: settingsFormRef, id: "gutenberg-sidebar-dialog" }, /* @__PURE__ */ React.createElement(Spinner, null))))), !contentBlockId && /* @__PURE__ */ React.createElement(Placeholder, { icon: "media-document", label: name }, /* @__PURE__ */ React.createElement("div", { className: "content-blocks__placeholder" }, /* @__PURE__ */ React.createElement("div", { className: "content-blocks__placeholder__description" }, /* @__PURE__ */ React.createElement("p", null, Drupal2.t("This content block is not configured.")), /* @__PURE__ */ React.createElement("p", null, Drupal2.t("Fill the form at the sidebar to configure it."))))), contentBlockId && /* @__PURE__ */ React.createElement(RenderedEntity, { id: contentBlockId, viewMode }));
  }
  window.DrupalGutenberg = window.DrupalGutenberg || {};
  window.DrupalGutenberg.Components = window.DrupalGutenberg.Components || {};
  window.DrupalGutenberg.Components.ContentBlock = ContentBlock;
})(Drupal, wp, drupalSettings);
