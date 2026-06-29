#!/usr/bin/env bash
# =============================================================================
# create-child.sh — genera un nuovo child theme a partire da ficus
#
# Uso:
#   ./scaffold/create-child.sh <nome>
#
# Esempio:
#   ./scaffold/create-child.sh lomais
#   → crea ../lomais-wp/ con struttura completa
#
# Prerequisiti:
#   - node / npm installati (usa la versione in .nvmrc se si usa nvm)
#   - wpt-ficus e il nuovo progetto devono stare nella stessa directory padre
# =============================================================================

set -e

CHILD_NAME="${1:-}"

if [ -z "$CHILD_NAME" ]; then
    echo "Uso: $0 <nome-child>"
    echo "Esempio: $0 lomais"
    exit 1
fi

FICUS_DIR="$(cd "$(dirname "$0")/.." && pwd)"
PARENT_DIR="$(dirname "$FICUS_DIR")"
PROJECT_DIR="$PARENT_DIR/${CHILD_NAME}-wp"
THEME_DIR="$PROJECT_DIR/wp-content/themes/$CHILD_NAME"

if [ -d "$PROJECT_DIR" ]; then
    echo "Errore: $PROJECT_DIR esiste già."
    exit 1
fi

echo ""
echo "Creo il child theme '$CHILD_NAME' in: $PROJECT_DIR"
echo ""

# --- Struttura directory ---
mkdir -p "$THEME_DIR/assets/fonts"
mkdir -p "$THEME_DIR/assets/scss/blocks"
mkdir -p "$THEME_DIR/assets/ts"
mkdir -p "$PROJECT_DIR/wp-content/themes/ficus"

# --- File del progetto (root) ---
cp "$FICUS_DIR/scaffold/project-template/docker-compose.yml" "$PROJECT_DIR/"
cp "$FICUS_DIR/scaffold/project-template/.env.example"       "$PROJECT_DIR/"
cp "$FICUS_DIR/scaffold/project-template/.gitignore"         "$PROJECT_DIR/"
cp "$FICUS_DIR/scaffold/project-template/.nvmrc"             "$PROJECT_DIR/"

# --- File del child theme ---
cp "$FICUS_DIR/scaffold/child-template/style.css"       "$THEME_DIR/"
cp "$FICUS_DIR/scaffold/child-template/functions.php"   "$THEME_DIR/"
cp "$FICUS_DIR/scaffold/child-template/theme.json"      "$THEME_DIR/"
cp "$FICUS_DIR/scaffold/child-template/package.json"    "$THEME_DIR/"
cp "$FICUS_DIR/scaffold/child-template/vite.config.ts"  "$THEME_DIR/"
cp "$FICUS_DIR/scaffold/child-template/tsconfig.json"   "$THEME_DIR/"
touch "$THEME_DIR/assets/fonts/.gitkeep"

cp "$FICUS_DIR/scaffold/child-template/assets/scss/main.scss"          "$THEME_DIR/assets/scss/"
cp "$FICUS_DIR/scaffold/child-template/assets/scss/_reset.scss"        "$THEME_DIR/assets/scss/"
cp "$FICUS_DIR/scaffold/child-template/assets/scss/_typography.scss"   "$THEME_DIR/assets/scss/"
cp "$FICUS_DIR/scaffold/child-template/assets/scss/_layout.scss"       "$THEME_DIR/assets/scss/"
cp "$FICUS_DIR/scaffold/child-template/assets/scss/_header.scss"       "$THEME_DIR/assets/scss/"
cp "$FICUS_DIR/scaffold/child-template/assets/scss/_footer.scss"       "$THEME_DIR/assets/scss/"
cp "$FICUS_DIR/scaffold/child-template/assets/scss/blocks/"*.scss      "$THEME_DIR/assets/scss/blocks/"
cp "$FICUS_DIR/scaffold/child-template/assets/ts/main.ts"              "$THEME_DIR/assets/ts/"

# --- Sostituisce CHILDNAME con il nome reale ---
find "$PROJECT_DIR" -type f \( -name "*.php" -o -name "*.json" -o -name "*.ts" -o -name "*.scss" -o -name "*.css" -o -name "*.yml" -o -name "*.md" \) \
    -exec sed -i.bak "s/CHILDNAME/$CHILD_NAME/g" {} \; \
    -exec rm -f {}.bak \;

# --- npm install nel child theme ---
echo "npm install..."
cd "$THEME_DIR" && npm install

echo ""
echo "✓ Child theme '$CHILD_NAME' creato con successo."
echo ""
echo "Prossimi passi:"
echo "  1. cd $PROJECT_DIR"
echo "  2. cp .env.example .env"
echo "  3. docker-compose up -d"
echo "  4. cd wp-content/themes/$CHILD_NAME && npm run dev"
echo ""
echo "  Nota: wpt-ficus deve trovarsi in $PARENT_DIR/wpt-ficus"
echo "  oppure aggiorna il volume in docker-compose.yml."
echo ""
