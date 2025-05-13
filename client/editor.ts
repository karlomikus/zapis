import { dropCursor, EditorView, keymap, lineNumbers, ViewPlugin, Decoration } from "@codemirror/view"
import { markdown, markdownLanguage } from "@codemirror/lang-markdown"
import { languages } from "@codemirror/language-data"
import { defaultKeymap, history, historyKeymap, indentWithTab } from "@codemirror/commands";
import { EditorState, RangeSetBuilder } from "@codemirror/state";
import { bracketMatching, defaultHighlightStyle, indentOnInput, syntaxHighlighting, syntaxTree } from "@codemirror/language";
import { closeBrackets } from "@codemirror/autocomplete";
import { highlightSelectionMatches } from "@codemirror/search";
import { hyperLink } from '@uiw/codemirror-extensions-hyper-link';

const mainNoteContent = document.querySelector('#note-initial-content') as HTMLTextAreaElement | null;

// Decorate fenced markdown code blocks
const decorateLines = (view: EditorView) => {
    const builder = new RangeSetBuilder<Decoration>()
    const tree = syntaxTree(view.state)

    for (const visibleRange of view.visibleRanges) {
        for (let position = visibleRange.from; position < visibleRange.to;) {
            const line = view.state.doc.lineAt(position)
            let inlineCode: { from: number, to: number, innerFrom: number, innerTo: number }

            tree.iterate({
                enter({ type, from, to }) {
                    if (type.name !== 'Document') {
                        if (['CodeBlock', 'FencedCode'].includes(type.name)) {
                            builder.add(line.from, line.from, Decoration.line({ attributes: { class: 'cm-zapis-codeblock' } }))

                            const openLine = view.state.doc.lineAt(from)
                            const closeLine = view.state.doc.lineAt(to)

                            if (openLine.number === line.number)
                                builder.add(line.from, line.from, Decoration.line({ attributes: { class: 'cm-zapis-open-codeblock' } }))

                            if (closeLine.number === line.number)
                                builder.add(line.from, line.from, Decoration.line({ attributes: { class: 'cm-zapis-close-codeblock' } }))

                            return false
                        } else if (type.name === 'ATXHeading1') {
                            builder.add(line.from, line.from, Decoration.line({ attributes: { class: 'cm-zapis-h1' } }))
                        } else if (type.name === 'ATXHeading2') {
                            builder.add(line.from, line.from, Decoration.line({ attributes: { class: 'cm-zapis-h2' } }))
                        } else if (type.name === 'ATXHeading3') {
                            builder.add(line.from, line.from, Decoration.line({ attributes: { class: 'cm-zapis-h3' } }))
                        } else if (type.name === 'ATXHeading4') {
                            builder.add(line.from, line.from, Decoration.line({ attributes: { class: 'cm-zapis-h4' } }))
                        } else if (type.name === 'ATXHeading5') {
                            builder.add(line.from, line.from, Decoration.line({ attributes: { class: 'cm-zapis-h5' } }))
                        } else if (type.name === 'ATXHeading6') {
                            builder.add(line.from, line.from, Decoration.line({ attributes: { class: 'cm-zapis-h6' } }))
                        } else if (type.name === 'InlineCode') {
                            // Store a reference for the last inline code node.
                            inlineCode = { from, to, innerFrom: from, innerTo: to }
                        } else if (type.name === 'CodeMark') {
                            // Make sure the code mark is a part of the previously stored inline code node.
                            if (from === inlineCode.from) {
                                inlineCode.innerFrom = to

                                builder.add(from, to, Decoration.mark({ attributes: { class: 'cm-zapis-open-inlinecode' } }))
                            } else if (to === inlineCode.to) {
                                inlineCode.innerTo = from

                                builder.add(inlineCode.innerFrom, inlineCode.innerTo, Decoration.mark({ attributes: { class: 'cm-zapis-inlinecode' } }))
                                builder.add(from, to, Decoration.mark({ attributes: { class: 'cm-zapis-close-inlinecode' } }))
                            }
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

export default {
    doc: mainNoteContent?.value ?? '',
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
        markdown({ base: markdownLanguage, codeLanguages: languages }),
        EditorView.lineWrapping,
        EditorState.allowMultipleSelections.of(true),
        codeBlockPlugin,
        hyperLink,
        keymap.of([
            ...defaultKeymap,
            ...historyKeymap,
            indentWithTab,
        ])
    ]
}