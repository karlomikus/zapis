import "./reset.css";
import "./styles.css";
// import "@milkdown/theme-nord/style.css";
// import "prism-themes/themes/prism-one-light.css";

declare global {
  interface Window {
    addNewNote: () => void;
    showCommandPanel: () => void;
    saveNote: (noteId: string) => Promise<void>;
  }
}

// import { Editor, rootCtx, defaultValueCtx } from "@milkdown/kit/core";
// import { commonmark } from "@milkdown/kit/preset/commonmark";
// import { gfm } from "@milkdown/kit/preset/gfm";
// import { nord } from "@milkdown/theme-nord";
// import { prism } from "@milkdown/plugin-prism";
// import { history } from "@milkdown/kit/plugin/history";
import { wrap } from 'ink-mde'

const mainNoteContent = document.querySelector('#note-content') as HTMLTextAreaElement;

wrap(mainNoteContent, {
  interface: {
    attribution: true,
    autocomplete: true,
    readonly: false,
    spellcheck: false,
    toolbar: false,
  },
})
// ink(document.getElementById('editor')!)
// Editor.make()
//   .config((ctx) => {
//     ctx.set(rootCtx, "#editor");
//     ctx.set(defaultValueCtx, mainNoteContent?.value);
//   })
//   .config(nord)
//   .use(commonmark)
//   .use(gfm)
//   .use(prism)
//   .use(history)
//   .create();

window.addNewNote = () => {
  const dialog = document.querySelector('#newNoteDialog') as HTMLDialogElement;
  if (!dialog) {
    console.error('Dialog not found');
    return;
  }

  const closeButton = dialog.querySelector('.close') as HTMLButtonElement;
  closeButton.addEventListener('click', () => {
    dialog.close();
  });

  dialog.showModal();
}

window.showCommandPanel = () => {
  const dialog = document.querySelector('#commandPanelDialog') as HTMLDialogElement;
  if (!dialog) {
    console.error('Dialog not found');
    return;
  }

  const closeButton = dialog.querySelector('.close') as HTMLButtonElement;
  closeButton.addEventListener('click', () => {
    dialog.close();
  });

  dialog.showModal();
}

window.saveNote = async (noteId: string) => {
  // POST request to /noteId
  // const content = (document.querySelector('#note-content') as HTMLTextAreaElement).value;
  const content = editor.getMarkdown();
  const title = (document.querySelector('#note-content') as HTMLHeadingElement).innerText;
  const statusBar = document.querySelector('#status-bar') as HTMLDivElement;
  const url = `/notes/${noteId}`;
  const method = 'POST';
  const options: RequestInit = {
    method,
    headers: {
      'Content-Type': 'text/plain',
    },
    body: content,
  };
  const response = await fetch(url, options)
  // if (response.ok) {
  //   statusBar.style.display = 'block';
  // }
}

document.addEventListener('keydown', e => {
  if (e.ctrlKey && e.key === 's') {
    // Prevent the Save dialog to open
    e.preventDefault();
    // Place your code here
    console.log('CTRL + S');
    // window.saveNote('tefx')
  }
});