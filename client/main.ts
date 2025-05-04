import "./styles.css";
import "./editor.css";
import '@toast-ui/editor/dist/toastui-editor-only.css';
import Editor from '@toast-ui/editor';

const editor = new Editor({
    el: document.querySelector('#editor'),
    initialEditType: 'markdown',
    hideModeSwitch: true,
    height: 'auto',
    theme: 'zapis',
    initialValue: document.querySelector('textarea')?.value,
});

const dialog = document.querySelector("dialog");
const showButton = document.querySelector("dialog + button");
const closeButton = document.querySelector("dialog button");

// "Show the dialog" button opens the dialog modally
showButton.addEventListener("click", () => {
  dialog.showModal();
});

// "Close" button closes the dialog
closeButton.addEventListener("click", () => {
  dialog.close();
});
