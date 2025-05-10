import "./reset.css";
import "./styles.css";
import "./editor.css";

import editorConfig from "./editor";
import { EditorView } from "@codemirror/view";

declare global {
  interface Window {
    syncSearch: () => void;
    addNewNote: () => void;
    showCommandPanel: () => void;
    saveNote: (noteId: string) => Promise<void>;
    deleteNote: (noteId: string) => Promise<void>;
    doSearch: (term: string) => Promise<void>;
  }
}

const editor = new EditorView(editorConfig)

function fadeOutElement(element: HTMLElement, duration: number) {
  element.style.opacity = '1';

  element.addEventListener('transitionend', () => {
    element.remove();
  }, { once: true });

  setTimeout(() => {
    element.style.transition = `opacity 250ms`;
    element.style.opacity = '0';
  }, duration);
}

function showToast(message: string) {
  const toast = document.createElement('div');
  toast.className = 'toasts-toast';
  toast.innerText = message;

  const toastsContainer = document.querySelector('.toasts') as HTMLDivElement;
  toastsContainer.appendChild(toast);

  fadeOutElement(toast, 750);
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
  if (response.ok) {
    showToast('Sync searched successfully');
  }
}

window.doSearch = async (term: string) => {
  const payload = {
    query: term,
  };

  let result = [];
  if (term) {
    result.push({ id: term + '.md', title: 'Create/open new note', path: term + '.md' })
  }

  const url = `/search`;
  const method = 'POST';
  const options: RequestInit = {
    method,
    body: JSON.stringify(payload),
  };
  const response = await fetch(url, options)
  const apiResults = await response.json();
  result = [...result, ...apiResults];
  renderSearchResults(result)
}

let searchTimeout: number | null = null;

document.getElementById('command-input')?.addEventListener('keyup', (e: KeyboardEvent) => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  searchTimeout = window.setTimeout(() => {
    window.doSearch((e.target as HTMLInputElement).value);
    searchTimeout = null;
  }, 200);
})

function renderSearchResults(results: any[]) {
  const resultsContainer = document.querySelector('#command-results') as HTMLDivElement;
  resultsContainer.innerHTML = ''; // Clear previous results
  results.forEach(result => {
    const resultItem = document.createElement('a');
    resultItem.href = `/notes/${result.id}`;
    resultItem.className = 'command-item';
    resultItem.innerHTML = `
      <h3>${result.title}</h3>
      <p>${result.path}</p>
    `;
    resultsContainer.appendChild(resultItem);
  });
}

window.saveNote = async (noteId: string) => {
  const content = editor.state.doc.toString();
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
  if (response.ok) {
    showToast('Note saved successfully');
  }
}

window.deleteNote = async (noteId: string) => {
  if (!confirm('Are you sure you want to delete this note?')) {
    return;
  }

  const url = `/notes/${noteId}`;
  const method = 'DELETE';
  const options: RequestInit = {
    method,
  };
  const response = await fetch(url, options)
  if (response.ok) {
    showToast('Note deleted successfully');
    window.location.href = '/';
  }
}

document.addEventListener('keydown', e => {
  const id = (document.querySelector('#note-id') as HTMLInputElement | null);
  if (!id) {
    return;
  }

  if (e.ctrlKey && e.key === 's') {
    e.preventDefault();
    window.saveNote(id.value)
  }
});