import "./styles.css";
import '@toast-ui/editor/dist/toastui-editor.css';
import Editor from '@toast-ui/editor';

const editor = new Editor({
    el: document.querySelector('#editor'),
    initialEditType: 'markdown',
    height: '1200px',
    initialValue: document.querySelector('textarea')?.value,
});
