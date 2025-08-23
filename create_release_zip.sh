#!/bin/bash

# プラグイン名とバージョンを取得
PLUGIN_NAME="jp-crossword"

# プラグインファイルからバージョンを動的に取得
VERSION=$(grep -o 'Version: [0-9]\+\.[0-9]\+\.[0-9]\+' jp-crossword.php | head -1 | sed 's/Version: //')

# バージョンが取得できない場合はデフォルト値を使用
if [ -z "$VERSION" ]; then
    VERSION="1.0.0"
    echo "警告: バージョン情報を取得できませんでした。デフォルト値 $VERSION を使用します。"
fi

TODAY=$(date +"%Y%m%d")

# 出力先ディレクトリ
OUTPUT_DIR="/Users/kantanpro/Desktop/Game_TEST_UP"

# zipファイル名
ZIP_NAME="${PLUGIN_NAME}_${VERSION}_${TODAY}.zip"

# 出力先ディレクトリが存在しない場合は作成
if [ ! -d "$OUTPUT_DIR" ]; then
    mkdir -p "$OUTPUT_DIR"
    echo "出力先ディレクトリを作成しました: $OUTPUT_DIR"
fi

# 一時作業ディレクトリを作成
TEMP_DIR=$(mktemp -d)
PLUGIN_DIR="$TEMP_DIR/$PLUGIN_NAME"

# プラグインディレクトリを作成
mkdir -p "$PLUGIN_DIR"

echo "配布用zipファイルを作成中..."

# 必要なファイルをコピー
cp jp-crossword.php "$PLUGIN_DIR/"
cp README.md "$PLUGIN_DIR/"
cp readme.txt "$PLUGIN_DIR/"

# assetsディレクトリとその内容をコピー
if [ -d "assets" ]; then
    cp -r assets "$PLUGIN_DIR/"
fi

# 作業ディレクトリに移動
cd "$TEMP_DIR"

# zipファイルを作成
zip -r "$ZIP_NAME" "$PLUGIN_NAME"

# 出力先に移動
mv "$ZIP_NAME" "$OUTPUT_DIR/"

# 一時ディレクトリを削除
rm -rf "$TEMP_DIR"

# 結果を表示
echo "配布用zipファイルが作成されました:"
echo "ファイル名: $ZIP_NAME"
echo "保存先: $OUTPUT_DIR"
echo "ファイルサイズ: $(du -h "$OUTPUT_DIR/$ZIP_NAME" | cut -f1)"

# 作成されたzipファイルの内容を確認
echo ""
echo "zipファイルの内容:"
unzip -l "$OUTPUT_DIR/$ZIP_NAME"
