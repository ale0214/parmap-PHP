<?php
session_start();
include 'conexion.php';

// Recibir la categoria si viene en la URL
// Por ejemplo: catalogo.php?categoria=Llantas
$categoria_filtro = isset($_GET['categoria']) ? $_GET['categoria'] : '';

// Si hay categoria filtramos, si no traemos todos
if ($categoria_filtro != '') {
    $sql = "SELECT * FROM productos WHERE categoria LIKE '%$categoria_filtro%' AND stock > 0 ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM productos WHERE stock > 0 ORDER BY id DESC";
}

$productos = mysqli_query($conexion, $sql);

// Traer todas las categorias unicas para el filtro del sidebar
$categorias = mysqli_query($conexion, "SELECT DISTINCT categoria FROM productos WHERE stock > 0");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - Almacén PARMAP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #FFF8F0; color: #333; }

        /* HEADER */
        header {
            background-color: white;
            padding: 12px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .header-logo img { width: 50px; }
        .header-logo-texto span { display: block; font-size: 11px; color: #999; text-transform: uppercase; }
        .header-logo-texto strong { display: block; font-size: 24px; color: #E87722; line-height: 1; }

        .buscador { display: flex; border: 2px solid #E87722; border-radius: 25px; overflow: hidden; width: 350px; }
        .buscador input { border: none; padding: 10px 18px; width: 100%; font-size: 14px; outline: none; }
        .buscador button { background-color: #E87722; border: none; padding: 10px 20px; cursor: pointer; color: white; }

        nav { display: flex; gap: 20px; align-items: center; }
        nav a { text-decoration: none; color: #333; font-size: 13px; font-weight: bold; }
        nav a:hover { color: #E87722; }
        .btn-login { background-color: #E87722; color: white !important; padding: 8px 20px; border-radius: 5px; }

        .banner-envios { background-color: #E87722; color: white; text-align: center; padding: 10px; font-size: 13px; font-weight: bold; letter-spacing: 2px; }

        /* BREADCRUMB - La ruta de navegacion */
        .breadcrumb {
            padding: 15px 40px;
            font-size: 13px;
            color: #999;
            background: white;
            border-bottom: 1px solid #eee;
        }
        .breadcrumb a { color: #E87722; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }

        /* LAYOUT PRINCIPAL - Sidebar + Productos */
        .layout {
            display: flex;
            max-width: 1300px;
            margin: 30px auto;
            padding: 0 30px;
            gap: 30px;
        }

        /* SIDEBAR DE CATEGORIAS */
        .sidebar {
            width: 240px;
            min-width: 240px;
        }

        .sidebar-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            margin-bottom: 20px;
        }

        .sidebar-card h3 {
            font-size: 14px;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #E87722;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .categoria-btn {
            display: block;
            padding: 10px 12px;
            border-radius: 8px;
            text-decoration: none;
            color: #555;
            font-size: 13px;
            margin-bottom: 5px;
            transition: 0.2s;
        }

        .categoria-btn:hover {
            background-color: #FBD8A8;
            color: #E87722;
            font-weight: bold;
        }

        /* La categoria activa se resalta */
        .categoria-btn.activa {
            background-color: #E87722;
            color: white;
            font-weight: bold;
        }

        /* AREA DE PRODUCTOS */
        .area-productos { flex: 1; }

        .area-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .area-header h2 {
            font-size: 20px;
            color: #333;
        }

        .area-header span {
            font-size: 13px;
            color: #999;
        }

        .productos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 20px;
        }

        .producto-card {
            background-color: #FBD8A8;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            transition: 0.3s;
        }

        .producto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .producto-icono { font-size: 50px; margin-bottom: 10px; }

        .producto-card h3 {
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .producto-card .categoria-tag {
            font-size: 11px;
            color: #E87722;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .producto-card .precio {
            font-size: 20px;
            font-weight: bold;
            color: #E87722;
            margin-bottom: 10px;
        }

        .producto-card .stock-ok {
            font-size: 11px;
            color: #27ae60;
            margin-bottom: 12px;
        }

        .btn-agregar {
            display: block;
            background-color: #1a1a1a;
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 13px;
            transition: 0.3s;
        }

        .btn-agregar:hover { background-color: #E87722; }

        .sin-productos {
            grid-column: 1/-1;
            text-align: center;
            padding: 60px;
            color: #999;
            font-size: 16px;
        }

        /* FOOTER */
        footer {
            background-color: #111;
            color: white;
            padding: 40px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 50px;
        }
        footer h4 { color: #E87722; margin-bottom: 15px; font-size: 14px; text-transform: uppercase; }
        footer p, footer a { color: #aaa; font-size: 13px; text-decoration: none; display: block; margin-bottom: 8px; }
        footer a:hover { color: #E87722; }
        .footer-bottom { background-color: #000; color: #555; text-align: center; padding: 15px; font-size: 12px; }

        @media (max-width: 768px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    <a href="tienda.php" class="header-logo">
        <img src="images/logo.png" alt="PARMAP">
        <div class="header-logo-texto">
            <span>Almacén</span>
            <strong>PARMAP</strong>
            <span>Partes para maquinaria pesada</span>
        </div>
    </a>
    <div class="buscador">
        <input type="text" id="buscar" placeholder="¿Qué estás buscando?" onkeyup="buscarProducto()">
        <button>🔍</button>
    </div>
    <!-- Reemplaza el enlace del carrito por esto: -->
<a href="carrito.php" style="text-decoration:none; color:#333; font-weight:bold; font-size:13px;">
    🛒 Carrito 
    <?php 
    $cant = isset($_SESSION['carrito']) ? array_sum(array_column($_SESSION['carrito'], 'cantidad')) : 0;
    if ($cant > 0) echo "<span style='background:#E87722; color:white; border-radius:50%; padding:2px 7px; font-size:11px;'>$cant</span>";
    ?>
</a>
    <nav>
        <a href="tienda.php">Inicio</a>
        <a href="catalogo.php">Catálogo</a>
        <?php if (isset($_SESSION['usuario'])): ?>
            <span style="color:#E87722; font-size:13px;">👋 <?php echo $_SESSION['nombre']; ?></span>
            <a href="logout.php" class="btn-login">Salir</a>
        <?php else: ?>
            <a href="registro.php" style="color:#E87722; font-size:13px; font-weight:bold;">Registrarse</a>
            <a href="index.php" class="btn-login">Iniciar Sesión</a>
        <?php endif; ?>
    </nav>
</header>

<div class="banner-envios">🚚 ENVÍOS EN MENOS DE 48 HORAS A TODO EL PAÍS</div>

<!-- BREADCRUMB -->
<div class="breadcrumb">
    <a href="tienda.php">Inicio</a> → 
    <a href="catalogo.php">Catálogo</a>
    <?php if ($categoria_filtro != ''): ?>
        → <strong style="color:#333;"><?php echo $categoria_filtro; ?></strong>
    <?php endif; ?>
</div>

<!-- LAYOUT PRINCIPAL -->
<div class="layout">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-card">
            <h3>Categorías</h3>
            <!-- Ver todos -->
            <a href="catalogo.php" class="categoria-btn <?php echo $categoria_filtro == '' ? 'activa' : ''; ?>">
                🔍 Ver todos
            </a>
            <!-- Categorias dinamicas desde la BD -->
            <?php
            $iconos_cat = [
                'Filtración' => '🔧', 'Transmisión' => '🔄',
                'Hidráulico' => '💧', 'Llantas' => '🔘',
                'Lubricantes' => '🛢️', 'Mangueras' => '🌀',
                'Aditamentos' => '🪣',
            ];
            while ($cat = mysqli_fetch_assoc($categorias)) {
                $nombre_cat = $cat['categoria'];
                $icono_cat = '⚙️';
                foreach ($iconos_cat as $key => $val) {
                    if (stripos($nombre_cat, $key) !== false) {
                        $icono_cat = $val;
                        break;
                    }
                }
                $activa = $categoria_filtro == $nombre_cat ? 'activa' : '';
                echo "<a href='catalogo.php?categoria=" . urlencode($nombre_cat) . "' 
                         class='categoria-btn $activa'>
                         $icono_cat $nombre_cat
                      </a>";
            }
            ?>
        </div>
    </div>

    <!-- PRODUCTOS -->
    <div class="area-productos">
        <div class="area-header">
            <h2>
                <?php echo $categoria_filtro != '' ? "📦 " . $categoria_filtro : "📦 Todos los productos"; ?>
            </h2>
            <span><?php echo mysqli_num_rows($productos); ?> productos encontrados</span>
        </div>

        <div class="productos-grid" id="productos-grid">
            <?php
            $iconos = [
                'Filtración' => '🔧', 'Transmisión' => '🔄',
                'Hidráulico' => '💧', 'Llantas' => '🔘',
                'Lubricantes' => '🛢️', 'Mangueras' => '🌀',
                'Aditamentos' => '🪣',
            ];

            if (mysqli_num_rows($productos) > 0) {
                while ($p = mysqli_fetch_assoc($productos)) {
                    $icono = '⚙️';
                    foreach ($iconos as $key => $val) {
                        if (stripos($p['categoria'], $key) !== false) {
                            $icono = $val;
                            break;
                        }
                    }
                    echo "
                    <div class='producto-card' data-nombre='" . strtolower(htmlspecialchars($p['nombre'])) . "'>
                        <div class='producto-icono'>$icono</div>
                        <h3>" . htmlspecialchars($p['nombre']) . "</h3>
                        <p class='categoria-tag'>" . htmlspecialchars($p['categoria']) . "</p>
                        <p class='precio'>\$" . number_format($p['precio'], 0, ',', '.') . "</p>
                        <p class='stock-ok'>✅ En stock (" . $p['stock'] . " und)</p>
                        <a href='carrito.php?id=" . $p['id'] . "' class='btn-agregar'>+ AGREGAR</a>
                    </div>";
                }
            } else {
                echo "<div class='sin-productos'>
                    😕 No hay productos en esta categoría todavía
                </div>";
            }
            ?>
        </div>
    </div>
</div>

<footer>
    <div>
        <h4>🔧 Almacén PARMAP</h4>
        <p>Repuestos originales para maquinaria pesada.</p>
    </div>
    <div>
        <h4>Contacto</h4>
        <p>📧 info@parmap.com</p>
        <p>📍 Ibagué, Tolima</p>
    </div>
    <div>
        <h4>Enlaces</h4>
        <a href="tienda.php">Inicio</a>
        <a href="catalogo.php">Catálogo</a>
        <a href="index.php">Iniciar Sesión</a>
    </div>
</footer>
<div class="footer-bottom">© 2026 Almacén PARMAP - Todos los derechos reservados</div>

<script>
    function buscarProducto() {
        const texto = document.getElementById('buscar').value.toLowerCase();
        document.querySelectorAll('.producto-card').forEach(t => {
            t.style.display = t.getAttribute('data-nombre').includes(texto) ? 'block' : 'none';
        });
    }
</script>

</body>
</html>