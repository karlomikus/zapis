import "./reset.css";
import "./styles.css";
import "./editor.css";

declare global {
  interface Window {
    syncSearch: () => void;
    addNewNote: () => void;
    showCommandPanel: () => void;
    saveNote: (noteId: string) => Promise<void>;
  }
}

import { dropCursor, EditorView, keymap, lineNumbers, ViewPlugin, Decoration } from "@codemirror/view"
import { markdown } from "@codemirror/lang-markdown"
import { languages } from "@codemirror/language-data"
import { history, historyKeymap } from "@codemirror/commands";
import { EditorState, RangeSetBuilder } from "@codemirror/state";
import { bracketMatching, defaultHighlightStyle, indentOnInput, syntaxHighlighting, syntaxTree } from "@codemirror/language";
import { closeBrackets } from "@codemirror/autocomplete";
import { highlightSelectionMatches } from "@codemirror/search";

const mainNoteContent = document.querySelector('#note-content') as HTMLTextAreaElement;

const codeBlockSyntaxNodes = [
  'CodeBlock',
  'FencedCode',
]
const codeBlockDecoration = Decoration.line({ attributes: { class: 'cm-zapis-codeblock' } })
const codeBlockOpenDecoration = Decoration.line({ attributes: { class: 'cm-zapis-open-codeblock' } })
const codeBlockCloseDecoration = Decoration.line({ attributes: { class: 'cm-zapis-close-codeblock' } })

// Decorate fenced markdown code blocks
const decorateLines = (view: EditorView) => {
  const builder = new RangeSetBuilder<Decoration>()
  const tree = syntaxTree(view.state)

  for (const visibleRange of view.visibleRanges) {
    for (let position = visibleRange.from; position < visibleRange.to;) {
      const line = view.state.doc.lineAt(position)

      tree.iterate({
        enter({ type, from, to }) {
          if (type.name !== 'Document') {
            if (codeBlockSyntaxNodes.includes(type.name)) {
              builder.add(line.from, line.from, codeBlockDecoration)

              const openLine = view.state.doc.lineAt(from)
              const closeLine = view.state.doc.lineAt(to)

              if (openLine.number === line.number)
                builder.add(line.from, line.from, codeBlockOpenDecoration)

              if (closeLine.number === line.number)
                builder.add(line.from, line.from, codeBlockCloseDecoration)

              return false
            }
          }
        },
        from: line.from,
        to: line.to,
      })

      position = line.to + 1
    }
  }

  return builder.finish()
}

const codeBlockPlugin = ViewPlugin.define((view: EditorView) => {
  return {
    update: () => {
      return decorateLines(view)
    },
  }
}, { decorations: plugin => plugin.update() })

new EditorView({
  doc: mainNoteContent.value,
  parent: document.getElementById('editor')!,
  extensions: [
    lineNumbers(),
    history(),
    dropCursor(),
    indentOnInput(),
    bracketMatching(),
    closeBrackets(),
    highlightSelectionMatches(),
    syntaxHighlighting(defaultHighlightStyle),
    markdown({ codeLanguages: languages }),
    EditorView.lineWrapping,
    EditorState.allowMultipleSelections.of(true),
    codeBlockPlugin,
    keymap.of([
      ...historyKeymap,
    ])
  ]
})

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

window.syncSearch = async () => {
  const url = `/sync-search`;
  const method = 'POST';
  const options: RequestInit = {
    method,
  };
  const response = await fetch(url, options)
}

// window.saveNote = async (noteId: string) => {
//   // POST request to /noteId
//   // const content = (document.querySelector('#note-content') as HTMLTextAreaElement).value;
//   const content = editor.getMarkdown();
//   const title = (document.querySelector('#note-content') as HTMLHeadingElement).innerText;
//   const statusBar = document.querySelector('#status-bar') as HTMLDivElement;
//   const url = `/notes/${noteId}`;
//   const method = 'POST';
//   const options: RequestInit = {
//     method,
//     headers: {
//       'Content-Type': 'text/plain',
//     },
//     body: content,
//   };
//   const response = await fetch(url, options)
//   // if (response.ok) {
//   //   statusBar.style.display = 'block';
//   // }
// }

document.addEventListener('keydown', e => {
  if (e.ctrlKey && e.key === 's') {
    // Prevent the Save dialog to open
    e.preventDefault();
    // Place your code here
    console.log('CTRL + S');
    // window.saveNote('tefx')
  }
});