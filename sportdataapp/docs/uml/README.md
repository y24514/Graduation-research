# UML レンダリング

このフォルダの `.puml` から、画像（SVG/PNG）を生成します。

## 生成方法
リポジトリルートで以下を実行:

- `node sportdataapp/docs/uml/render-plantuml.cjs`

生成物は `sportdataapp/docs/uml/out/` に出力されます。

## 前提
- Node.js 18+（本環境ではOK）
- インターネット接続（PlantUML公開サーバを利用）

## 生成されるファイル
- `out/usecase.(png|svg)`
- `out/er.(png|svg)`
- `out/er-core.(png|svg)`
- `out/er-chat.(png|svg)`
- `out/er-swim.(png|svg)`
- `out/er-basketball.(png|svg)`
- `out/er-tennis.(png|svg)`
- `out/sequence-login.(png|svg)`
- `out/sequence-chat-send.(png|svg)`
- `out/sequence-diary-submit-feedback.(png|svg)`
