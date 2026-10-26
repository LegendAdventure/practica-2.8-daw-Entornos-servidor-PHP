#!/usr/bin/env bash
#
# test.sh — Ejecuta casos de prueba automáticos del tablón de anuncios.
# Uso: ./test.sh
#
# Leyenda:
#   ✅ Pasa
#   ❌ Falla
#   ⚠️  Pasa con matices / requiere revisión manual

BASE="http://localhost:8080"
COOKIES="/tmp/tablon_cookies.txt"
HTML="/tmp/tablon_login.html"

PASS=0
FAIL=0
WARN=0

# Colores (si la terminal los soporta)
GREEN="\033[0;32m"
RED="\033[0;31m"
YELLOW="\033[0;33m"
NC="\033[0m"

check() {
    local id="$1"
    local desc="$2"
    local expected="$3"
    local actual="$4"
    local result

    if [[ "$actual" == "$expected" ]]; then
        echo -e "${GREEN}✅${NC} [$id] $desc → $actual"
        PASS=$((PASS + 1))
    else
        echo -e "${RED}❌${NC} [$id] $desc → esperado $expected, obtenido $actual"
        FAIL=$((FAIL + 1))
    fi
}

manual() {
    local id="$1"
    local desc="$2"
    echo -e "${YELLOW}⚠️${NC}  [$id] $desc → requiere revisión manual"
    WARN=$((WARN + 1))
}

code() {
    # Devuelve el código HTTP de una URL
    curl -s -o /dev/null -w "%{http_code}" "$1"
}

echo "=============================================="
echo " Ejecutando casos de prueba — Tablón de anuncios"
echo " Base: $BASE"
echo "=============================================="
echo ""

# ---------------------------------------------------------------
# 1. Rutas básicas (GET)
# ---------------------------------------------------------------
echo "--- 1. Rutas básicas ---"

check "L1" "GET /posts" "200" "$(code "$BASE/posts")"
check "L2" "GET /posts/show/1 (ruta limpia)" "200" "$(code "$BASE/posts/show/1")"
check "L3" "GET /posts/show?id=1 (compatibilidad)" "200" "$(code "$BASE/posts/show?id=1")"
check "L4" "GET /login" "200" "$(code "$BASE/login")"
check "L5" "GET /register" "200" "$(code "$BASE/register")"
check "L6" "GET /posts/show/999 (inexistente)" "404" "$(code "$BASE/posts/show/999")"
check "L7" "GET /no-existe (404)" "404" "$(code "$BASE/no-existe")"

echo ""

# ---------------------------------------------------------------
# 2. CSRF (POST sin token)
# ---------------------------------------------------------------
echo "--- 2. CSRF (POST sin token) ---"

check "X1" "POST /login sin token" "403" "$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/login" -d "email=a@a.com&password=1234")"
check "X2" "POST /register sin token" "403" "$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/register" -d "email=a@a.com&username=a&password=1234")"
check "X3" "POST /posts/store sin token" "403" "$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/posts/store" -d "title=t&body=b&category=c")"
check "X4" "POST /comments/store sin token" "403" "$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/comments/store" -d "post_id=1&body=hola")"

echo ""

# ---------------------------------------------------------------
# 3. Login con token
# ---------------------------------------------------------------
echo "--- 3. Login con token ---"

# Obtener cookie + token
curl -s -c "$COOKIES" "$BASE/login" -o "$HTML"
TOKEN=$(grep -oP 'name="csrf_token" value="\K[^"]+' "$HTML" 2>/dev/null || grep -o 'name="csrf_token" value="[^"]*"' "$HTML" | sed 's/.*value="//;s/"//')

if [[ -z "$TOKEN" ]]; then
    echo -e "${RED}❌${NC} No se pudo extraer el token CSRF. Abortando login."
    FAIL=$((FAIL + 1))
else
    echo "Token obtenido: ${TOKEN:0:16}..."

    # Login con credenciales correctas (lolaalbara@pipo.com / root)
    LOGIN_CODE=$(curl -s -o /dev/null -w "%{http_code}" -b "$COOKIES" -X POST "$BASE/login" \
        -d "email=lolaalbara@pipo.com" \
        -d "password=root" \
        -d "csrf_token=$TOKEN")

    if [[ "$LOGIN_CODE" == "302" ]]; then
        echo -e "${GREEN}✅${NC} [A5] Login correcto (lolaalbara@pipo.com) → 302"
        PASS=$((PASS + 1))
    else
        echo -e "${RED}❌${NC} [A5] Login correcto → esperado 302, obtenido $LOGIN_CODE"
        FAIL=$((FAIL + 1))
    fi

    # Login con contraseña incorrecta
    curl -s -c "$COOKIES" "$BASE/login" -o "$HTML"
    TOKEN=$(grep -oP 'name="csrf_token" value="\K[^"]+' "$HTML" 2>/dev/null || grep -o 'name="csrf_token" value="[^"]*"' "$HTML" | sed 's/.*value="//;s/"//')

    LOGIN_BAD=$(curl -s -o /dev/null -w "%{http_code}" -b "$COOKIES" -X POST "$BASE/login" \
        -d "email=lolaalbara@pipo.com" \
        -d "password=malamala" \
        -d "csrf_token=$TOKEN")

    if [[ "$LOGIN_BAD" == "302" ]]; then
        # 302 a /login (con error) o a / (con éxito). Comprobamos dónde redirige.
        LOCATION=$(curl -s -o /dev/null -w "%{redirect_url}" -b "$COOKIES" -X POST "$BASE/login" \
            -d "email=lolaalbara@pipo.com" \
            -d "password=malamala" \
            -d "csrf_token=$TOKEN")
        if [[ "$LOCATION" == *"/login"* ]]; then
            echo -e "${GREEN}✅${NC} [A6] Login con contraseña incorrecta → redirige a /login"
            PASS=$((PASS + 1))
        else
            echo -e "${RED}❌${NC} [A6] Login con contraseña incorrecta → redirige a $LOCATION (esperado /login)"
            FAIL=$((FAIL + 1))
        fi
    else
        echo -e "${RED}❌${NC} [A6] Login con contraseña incorrecta → esperado 302, obtenido $LOGIN_BAD"
        FAIL=$((FAIL + 1))
    fi
fi

echo ""

# ---------------------------------------------------------------
# 4. Búsqueda
# ---------------------------------------------------------------
echo "--- 4. Búsqueda ---"

SEARCH_PHP=$(curl -s "$BASE/posts?q=php")
if echo "$SEARCH_PHP" | grep -q "Busco grupo para proyecto de PHP"; then
    echo -e "${GREEN}✅${NC} [S1] /posts?q=php → contiene el post de PHP"
    PASS=$((PASS + 1))
else
    echo -e "${RED}❌${NC} [S1] /posts?q=php → no contiene el post esperado"
    FAIL=$((FAIL + 1))
fi

SEARCH_NONE=$(curl -s "$BASE/posts?q=xyz")
if echo "$SEARCH_NONE" | grep -q "No hay publicaciones"; then
    echo -e "${GREEN}✅${NC} [S3] /posts?q=xyz → muestra 'No hay publicaciones'"
    PASS=$((PASS + 1))
else
    echo -e "${RED}❌${NC} [S3] /posts?q=xyz → no muestra el mensaje esperado"
    FAIL=$((FAIL + 1))
fi

echo ""

# ---------------------------------------------------------------
# 5. Casos que requieren navegador (manual)
# ---------------------------------------------------------------
echo "--- 5. Casos manuales (requieren navegador) ---"

manual "A1"  "Registro con datos válidos"
manual "A2"  "Registro con email duplicado"
manual "A3"  "Registro con email inválido"
manual "A4"  "Registro con campos vacíos"
manual "A7"  "Login con email inexistente"
manual "A8"  "Login con campos vacíos"
manual "A9"  "Logout"
manual "A10" "Acceso a /posts/create sin sesión"
manual "P1"  "Crear post con datos válidos"
manual "P2"  "Crear post con campos vacíos"
manual "P5"  "Ver listado /posts"
manual "P6"  "Ver detalle /posts/show/1"
manual "P8"  "Editar propio post"
manual "P10" "Borrar propio post"
manual "R1"  "Intento de editar post de otro usuario (403)"
manual "R2"  "Intento de actualizar post de otro usuario (403)"
manual "R3"  "Intento de borrar post de otro usuario (403)"
manual "C1"  "Ver detalle con comentarios"
manual "C2"  "Ver detalle sin comentarios"
manual "C3"  "Comentar un post (logueado)"
manual "C4"  "Comentar con cuerpo vacío"
manual "C5"  "Sin sesión, no aparece formulario de comentario"
manual "C6"  "Sin sesión, POST /comments/store → /login"
manual "E1"  "XSS en título de post (se muestra como texto)"
manual "E2"  "XSS en comentario (se muestra como texto)"

echo ""

# ---------------------------------------------------------------
# Resumen
# ---------------------------------------------------------------
echo "=============================================="
echo " RESUMEN"
echo "=============================================="
echo -e "${GREEN}✅ Pasan:${NC}    $PASS"
echo -e "${RED}❌ Fallan:${NC}   $FAIL"
echo -e "${YELLOW}⚠️  Manuales:${NC} $WARN"
echo "----------------------------------------------"
TOTAL=$((PASS + FAIL + WARN))
echo "Total: $TOTAL casos ($PASS automáticos OK, $FAIL fallos, $WARN manuales)"
echo ""

if [[ $FAIL -eq 0 ]]; then
    echo -e "${GREEN}🎉 No hay fallos automáticos. Revisa los manuales en el navegador.${NC}"
    exit 0
else
    echo -e "${RED}⚠️  Hay $FAIL fallo(s). Revisa la salida arriba.${NC}"
    exit 1
fi