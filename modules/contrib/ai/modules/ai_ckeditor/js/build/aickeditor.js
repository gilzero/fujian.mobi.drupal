!function(t,e){"object"==typeof exports&&"object"==typeof module?module.exports=e():"function"==typeof define&&define.amd?define([],e):"object"==typeof exports?exports.CKEditor5=e():(t.CKEditor5=t.CKEditor5||{},t.CKEditor5.aickeditor=e())}(self,(()=>(()=>{var t={"ckeditor5/src/core.js":(t,e,i)=>{t.exports=i("dll-reference CKEditor5.dll")("./src/core.js")},"ckeditor5/src/ui.js":(t,e,i)=>{t.exports=i("dll-reference CKEditor5.dll")("./src/ui.js")},"ckeditor5/src/utils.js":(t,e,i)=>{t.exports=i("dll-reference CKEditor5.dll")("./src/utils.js")},"dll-reference CKEditor5.dll":t=>{"use strict";t.exports=CKEditor5.dll}},e={};function i(s){var o=e[s];if(void 0!==o)return o.exports;var r=e[s]={exports:{}};return t[s](r,r.exports,i),r.exports}i.d=(t,e)=>{for(var s in e)i.o(e,s)&&!i.o(t,s)&&Object.defineProperty(t,s,{enumerable:!0,get:e[s]})},i.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e);var s={};return(()=>{"use strict";i.d(s,{default:()=>u});var t=i("ckeditor5/src/core.js"),e=i("ckeditor5/src/ui.js");const o='<svg enable-background="new 0 0 128 128" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg"><path d="m121.59 60.83-13.93-4.49c-8.91-2.94-14.13-10.15-16.58-19.21l-6.13-29.86c-.16-.59-.55-1.38-1.75-1.38-1.01 0-1.59.79-1.75 1.38l-6.13 29.87c-2.46 9.06-7.67 16.27-16.58 19.21l-13.93 4.49c-1.97.64-2 3.42-.04 4.09l14.03 4.83c8.88 2.95 14.06 10.15 16.52 19.17l6.14 29.53c.16.59.49 1.65 1.75 1.65 1.33 0 1.59-1.06 1.75-1.65l6.14-29.53c2.46-9.03 7.64-16.23 16.52-19.17l14.03-4.83c1.94-.68 1.91-3.46-.06-4.1z" fill="#000000"/><path d="m122.91 62.08c-.22-.55-.65-1.03-1.32-1.25l-13.93-4.49c-8.91-2.94-14.13-10.15-16.58-19.21l-6.13-29.86c-.09-.34-.41-.96-.78-1.14l1.98 29.97c1.47 13.68 2.73 20.12 13.65 22 9.38 1.62 20.23 3.48 23.11 3.98z" fill="#000000"/><path d="m122.94 63.64-24.16 5.54c-8.51 2.16-13.2 7.09-13.2 19.99l-2.37 30.94c.81-.08 1.47-.52 1.75-1.65l6.14-29.53c2.46-9.03 7.64-16.23 16.52-19.17l14.03-4.83c.66-.24 1.08-.73 1.29-1.29z" fill="#000000"/><path d="m41.81 86.81c-8.33-2.75-9.09-5.85-10.49-11.08l-3.49-12.24c-.21-.79-2.27-.79-2.49 0l-2.37 11.31c-1.41 5.21-4.41 9.35-9.53 11.04l-8.16 3.54c-1.13.37-1.15 1.97-.02 2.35l8.22 2.91c5.1 1.69 8.08 5.83 9.5 11.02l2.37 10.82c.22.79 2.27.79 2.48 0l2.78-10.77c1.41-5.22 3.57-9.37 10.5-11.07l7.72-2.91c1.13-.39 1.12-1.99-.02-2.36z" fill="#000000"/><path d="m28.49 75.55c.85 7.86 1.28 10.04 7.65 11.67l13.27 2.59c-.14-.19-.34-.35-.61-.43l-7-2.57c-7.31-2.5-9.33-5.68-10.7-12.04s-2.83-10.51-2.83-10.51c-.51-1.37-1.24-1.3-1.24-1.3z" fill="#000000"/><g fill="#000000"><path d="m28.73 102.99c0-7.41 4.05-11.08 10.49-11.08l10.02-.41s-.58.77-1.59 1.01l-6.54 2.13c-5.55 2.23-8.08 3.35-9.8 10.94 0 0-2.22 8.83-2.64 9.76-.58 1.3-1.27 1.57-1.27 1.57z"/><path d="m59.74 28.14c.56-.19.54-.99-.03-1.15l-7.72-2.08c-1.62-.44-2.88-1.69-3.34-3.3l-3.04-12.55c-.15-.61-1.02-.61-1.17.01l-2.86 12.5c-.44 1.66-1.74 2.94-3.4 3.37l-7.67 1.99c-.57.15-.61.95-.05 1.15l8.09 2.8c1.45.5 2.57 1.68 3.01 3.15l2.89 11.59c.15.6 1.01.61 1.16 0l2.99-11.63c.45-1.47 1.58-2.64 3.04-3.13z" stroke="#000000" stroke-miterlimit="10"/></g></svg>';var r=i("ckeditor5/src/utils.js");class a extends t.Command{constructor(t){super(t)}execute(t,e,i){const s=this.editor.config.get("ai_ckeditor_ai"),{dialogURL:o,openDialog:r,dialogSettings:a={}}=s;if(!o||"function"!=typeof r)return;const n=this.editor.editing.model.getSelectedContent(this.editor.model.document.selection),d=this.editor.data.stringify(n);a.title=a.title+" - "+i;const l=new URL(o,document.baseURI);d.length>0&&l.searchParams.append("selected_text",d),l.searchParams.append("editor_id",this.editor.sourceElement.dataset.editorActiveTextFormat),l.searchParams.append("plugin_id",e),r(l.toString(),(({attributes:t})=>{const e=this.editor.model;e.change((i=>{const s=e.document.selection;s.getFirstPosition();if(s.hasOwnRange){const t=s.getFirstRange();i.remove(t)}if(void 0!==t.returnsHtml&&t.returnsHtml){const e=this.editor.data.processor.toView(t.value),i=this.editor.data.toModel(e);this.editor.model.insertContent(i)}else this.editor.model.insertContent(i.createText(t.value))}))}),a)}refresh(){const t=document.getElementsByClassName("ckeditor5-ai-ckeditor-dialog-form");this.isEnabled=0===t.length,this.isOn=this.isEnabled,this.isReadOnly=this.isEnabled}}class n extends t.Plugin{constructor(t){super(t),this.set("status",Drupal.t("Idle"))}init(){this.editor.sourceElement.parentElement.appendChild(this.statusContainer()),this.on("ai_status",((t,e)=>{this._setStatus(e)}))}statusContainer(){this.editor.t;const t=e.Template.bind(this,this),i=[];return this._outputView||(this._outputView=new e.View,this.bind("_ai_status").to(this,"status",(t=>Drupal.t("AI Writer: @status",{"@status":t}))),i.push({tag:"div",children:[{text:[t.to("_ai_status")]}],attributes:{class:"ck-ai-status__activity"}}),this._outputView.setTemplate({tag:"div",attributes:{class:["ck","ck-ai-status"]},children:i}),this._outputView.render()),this._outputView.element}destroy(){this._outputView&&(this._outputView.element.remove(),this._outputView.destroy()),super.destroy()}_setStatus(t){this.set("status",t.status)}}class d extends t.Command{constructor(t){super(t),this._status=this.editor.plugins.get(n)}execute(t){const e=this._status;e.fire("ai_status",{status:"Waiting for response..."});const i=this.editor,s=i.plugins.get("SourceEditing");i.enableReadOnlyMode("ai_ckeditor"),s.set("isSourceEditingMode",!0),s.isEnabled=!1;const o=i.editing.view.getDomRoot()?.nextSibling?.firstChild;o&&(o.value=""),i.model.change((async r=>{const a=await fetch(drupalSettings.path.baseUrl+"api/ai-ckeditor/request/"+t.editor_id+"/"+t.plugin_id,{method:"POST",credentials:"same-origin",body:JSON.stringify(t)});a.ok||(e.fire("ai_status",{status:"An error occurred. Check the logs for details."}),setTimeout((()=>{e.fire("ai_status",{status:"Idle"})}),3e3)),e.fire("ai_status",{status:"Receiving response..."});const n=a.body.getReader();for(;;){const{value:t,done:r}=await n.read(),a=(new TextDecoder).decode(t);if(r){e.fire("ai_status",{status:"All done!"}),setTimeout((()=>{e.fire("ai_status",{status:"Idle"})}),1e3);break}e.fire("ai_status",{status:"Writing..."});let d=o.value;o.value=d+a,i.setData(o.value),s.updateEditorData()}s.set("isSourceEditingMode",!1),s.isEnabled=!0,i.disableReadOnlyMode("ai_ckeditor")}))}}class l extends t.Plugin{init(){const t=this.editor;this.editor.config.get("ai_ckeditor_ai")&&(t.commands.add("AiDrupalDialog",new a(t)),t.commands.add("AiWriter",new d(t)),t.ui.componentFactory.add("aickeditor",(i=>{const s=new r.Collection,a=new e.ButtonView(i),n=this.editor.config.get("ai_ckeditor_ai");void 0!==n.plugins&&Object.keys(n.plugins).forEach((function(t){n.plugins[t].enabled&&s.add({type:"button",model:new e.ViewModel({isEnabled:n.plugins[t].enabled,label:n.plugins[t].meta.label,withText:!0,command:"AiDrupalDialog",group:"ai_ckeditor_ai",plugin_id:t})})}));const d=(0,e.createDropdown)(i,e.DropdownButtonView);return(0,e.addListToDropdown)(d,s),d.buttonView.set({label:"AI Assistant",class:"ai-dropdown",icon:o,tooltip:!0,withText:!0}),a.set({label:Drupal.t("AI Assistant"),icon:o,tooltip:!0,class:"ai-dropdown",withText:!0}),d.bind("isOn","isEnabled").to(t.commands.get("AiDrupalDialog"),"value","isEnabled"),a.bind("isOn","isEnabled").to(t.commands.get("AiDrupalDialog"),"value","isEnabled"),this.listenTo(d,"execute",(t=>{this.editor.execute(t.source.command,t.source.group,t.source.plugin_id,t.source.label)})),d})))}}class c extends t.Plugin{static get requires(){return[l,n]}}const u={AiCKEditor:c}})(),s=s.default})()));