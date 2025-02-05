/**
 * Generated by a build script. Do not modify.
 * Check orginal .jsx file.
 */
/* eslint-disable */

((wp2, $, drupalSettings2, Drupal2) => {
  const withNativeDialog = (Component) => {
    const onDialogCreate = (element, multiple) => {
      drupalSettings2.media_library = drupalSettings2.media_library || {};
      drupalSettings2.media_library.selection_remaining = multiple ? 1e3 : 1;
      setTimeout(() => {
        $("#media-library-wrapper li:first-child a").click();
      }, 0);
    };
    const getDefaultMediaSelections = () => (Drupal2.MediaLibrary.currentSelection || []).filter(
      (selection) => +selection
    );
    const getSpecialMediaSelections = () => [...Drupal2.SpecialMediaSelection.currentSelection || []].map(
      (selection) => JSON.stringify({
        [selection.processor]: selection.data
      })
    );
    async function onDialogInsert(element, props) {
      const { onSelect, handlesMediaEntity, multiple } = props;
      let selections = [
        ...getDefaultMediaSelections(),
        ...getSpecialMediaSelections()
      ];
      selections = multiple ? selections : selections.slice(0, 1);
      const endpointUrl = handlesMediaEntity ? Drupal2.url("editor/media/render") : Drupal2.url("editor/media/load-media");
      let selectionData = await Promise.all(
        selections.map(
          (selection) => fetch(
            `${endpointUrl}/${encodeURIComponent(selection)}`
          ).then((response) => response.json())
        )
      );
      if (handlesMediaEntity) {
        selectionData = selectionData.map(
          (selectionItem) => selectionItem.media_entity && selectionItem.media_entity.id
        );
      }
      onSelect(multiple ? selectionData : selectionData[0]);
    }
    const onDialogClose = () => {
      const modal = document.getElementById("media-entity-browser-modal");
      if (modal) {
        modal.remove();
      }
      const nodes = document.querySelectorAll(
        '[aria-describedby="media-entity-browser-modal"]'
      );
      nodes.forEach((node) => node.remove());
    };
    const getDialog = ({ allowedTypes, allowedBundles }) => new Promise((resolve, reject) => {
      wp2.apiFetch({
        path: "load-media-library-dialog",
        data: { allowedTypes, allowedBundles }
      }).then((result) => {
        resolve({
          component: (props) => /* @__PURE__ */ React.createElement(
            "div",
            {
              ...props,
              dangerouslySetInnerHTML: { __html: result.html }
            }
          )
        });
      }).catch((reason) => {
        reject(reason);
      });
    });
    return (props) => /* @__PURE__ */ React.createElement(
      Component,
      {
        ...props,
        onDialogCreate,
        onDialogInsert,
        onDialogClose,
        getDialog
      }
    );
  };
  window.DrupalGutenberg = window.DrupalGutenberg || {};
  window.DrupalGutenberg.Components = window.DrupalGutenberg.Components || {};
  window.DrupalGutenberg.Components.withNativeDialog = withNativeDialog;
})(wp, jQuery, drupalSettings, Drupal);
