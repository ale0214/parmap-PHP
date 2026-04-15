<?php
session_start();
include 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacén PARMAP - Repuestos para Maquinaria Pesada</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #fcfcfc; color: #333; }

        /* ── HEADER ── */
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

        .header-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .header-logo img { width: 55px; }

        .header-logo-texto span {
            display: block;
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-logo-texto strong {
            display: block;
            font-size: 26px;
            color: #E87722;
            line-height: 1;
        }

        .buscador {
            display: flex;
            border: 2px solid #E87722;
            border-radius: 25px;
            overflow: hidden;
            width: 380px;
        }

        .buscador input {
            border: none;
            padding: 10px 18px;
            width: 100%;
            font-size: 14px;
            outline: none;
            background: transparent;
        }

        .buscador button {
            background-color: #E87722;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            color: white;
            font-size: 16px;
        }

        nav { display: flex; gap: 20px; align-items: center; }

        nav a {
            text-decoration: none;
            color: #333;
            font-size: 13px;
            font-weight: bold;
        }

        nav a:hover { color: #E87722; }

        .btn-login {
            background-color: #E87722;
            color: white !important;
            padding: 8px 20px;
            border-radius: 5px;
        }

        .btn-login:hover { background-color: #1a1a1a !important; }

        /* ── BARRA ENVIOS ── */
        .banner-envios {
            background-color: #E87722;
            color: white;
            text-align: center;
            padding: 10px;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        /* ── HERO ── */
        .hero {
            height: 420px;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)),
                        url('images/head.png');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-bottom: 6px solid #E87722;
        }

        .hero-contenido h1 {
            font-size: 52px;
            color: white;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.8);
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .hero-contenido p {
            color: #ddd;
            font-size: 18px;
            margin-bottom: 25px;
        }

        .btn-ver-catalogo {
            background-color: #E87722;
            color: white;
            padding: 12px 35px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-ver-catalogo:hover { background-color: white; color: #E87722; }

        /* ── SECCIONES ── */
        .seccion { padding: 55px 40px; }
        .seccion-titulo {
            font-size: 26px;
            font-weight: 900;
            margin-bottom: 35px;
            color: #222;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .seccion-titulo::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background-color: #E87722;
            margin: 10px auto 0;
            border-radius: 2px;
        }

        /* ── CATEGORIAS ── */
        .categorias-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .categoria-card {
            background-color: #F5A623;
            border-radius: 16px;
            padding: 25px 15px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .categoria-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 25px rgba(232,119,34,0.35);
            background-color: #E87722;
        }

        .categoria-card .plus {
            position: absolute;
            top: 10px;
            right: 12px;
            font-size: 20px;
            font-weight: bold;
        }

        .categoria-card img {
            width: 100%;
            height: 120px;
            object-fit: contain;
            margin-bottom: 12px;
        }

        .categoria-card h3 {
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ── PRODUCTOS ── */
        .bg-crema { background-color: #FFF8F0; }

        .productos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .producto-card {
            background-color: #FBD8A8;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            transition: 0.3s;
        }

        .producto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .producto-icono { font-size: 55px; margin-bottom: 12px; }

        .producto-card h3 {
            font-size: 15px;
            color: #333;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .producto-card .categoria-tag {
            font-size: 11px;
            color: #E87722;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .producto-card .precio {
            font-size: 22px;
            font-weight: bold;
            color: #E87722;
            margin-bottom: 15px;
        }

        .producto-card .stock-ok {
            font-size: 12px;
            color: #27ae60;
            margin-bottom: 12px;
        }

        .producto-card .stock-agotado {
            font-size: 12px;
            color: #e74c3c;
            margin-bottom: 12px;
        }

        .btn-agregar {
            display: block;
            background-color: #1a1a1a;
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 13px;
            transition: 0.3s;
        }

        .btn-agregar:hover { background-color: #E87722; }

        /* ── MARCAS ── */
        .marcas-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .marca-card {
            background: white;
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 20px 10px;
            text-align: center;
            font-weight: 900;
            font-size: 14px;
            color: #333;
            transition: 0.3s;
            letter-spacing: 1px;
        }

        .marca-card:hover {
            border-color: #E87722;
            color: #E87722;
            transform: translateY(-3px);
        }

        /* ── FOOTER ── */
        footer {
            background-color: #111;
            color: white;
            padding: 50px 40px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 0;
        }

        footer h4 {
            color: #E87722;
            margin-bottom: 15px;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        footer p, footer a {
            color: #aaa;
            font-size: 13px;
            text-decoration: none;
            display: block;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        footer a:hover { color: #E87722; }

        .footer-bottom {
            background-color: #000;
            color: #555;
            text-align: center;
            padding: 15px;
            font-size: 12px;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .categorias-grid { grid-template-columns: repeat(2, 1fr); }
            .marcas-grid { grid-template-columns: repeat(3, 1fr); }
            .buscador { width: 220px; }
            footer { grid-template-columns: 1fr; }
        }

        @media (max-width: 600px) {
            .categorias-grid { grid-template-columns: repeat(2, 1fr); }
            .productos-grid { grid-template-columns: repeat(1, 1fr); }
            .marcas-grid { grid-template-columns: repeat(2, 1fr); }
            header { flex-wrap: wrap; gap: 10px; }
            .buscador { width: 100%; }
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
        <a href="catalogo.php">Catálogo</a>
        <a href="#marcas">Marcas</a>
        <a href="#contacto">Contacto</a>
    
        <?php if (isset($_SESSION['usuario'])): ?>
    <a href="perfil.php">👋 <?php echo $_SESSION['nombre']; ?></a>
    <?php if (($_SESSION['rol'] ?? '') === 'cliente'): ?>
    <a href="mis_pedidos.php">Mis pedidos</a>
    <?php endif; ?>
    <a href="logout.php">Salir</a>
<?php else: ?>
    <a href="index.php">Iniciar Sesión</a>
<?php endif; ?>
    </nav>
</header>

<!-- BARRA ENVIOS -->
<div class="banner-envios">🚚 ENVÍOS EN MENOS DE 48 HORAS A TODO EL PAÍS</div>

<!-- HERO -->
<div class="hero">
    <div class="hero-contenido">
        <h1>Almacén PARMAP</h1>
        <p>Repuestos originales para maquinaria pesada</p>
        <a href="catalogo.php" class="btn-ver-catalogo">Ver Catálogo</a>
    </div>
</div>

<!-- CATEGORIAS -->
<div class="seccion">
    <h2 class="seccion-titulo">Productos Destacados</h2>
    <div class="categorias-grid">
        <a href="catalogo.php?categoria=Llantas" class="categoria-card">
            <span class="plus">+</span>
            <img src="images/1000072025_da1a8538d3c44303025899dc8b0a2746-10_6_2025,_4_54_...png" alt="Llantas">
            <h3>Llantas y Rines</h3>
        </a>
        <a href="catalogo.php?categoria=Componentes" class="categoria-card">
            <span class="plus">+</span>
            <img src="images/1000072023_363388c333965dc6b7a4f11542885ccf-10_6_2025,_4_54_4...png" alt="Componentes">
            <h3>Componentes Mayores</h3>
        </a>
        <a href="catalogo.php?categoria=Transmisión" class="categoria-card">
            <span class="plus">+</span>
            <img src="images/1000072027_1d617e617cfefd4fe1170b87a0fcd8d-10_6_2025,_4_34_1...png" alt="Transmisión">
            <h3>Transmisión</h3>
        </a>
        <a href="catalogo.php?categoria=Filtración" class="categoria-card">
            <span class="plus">+</span>
            <img src="images/1000072021_cd7a06a6617f6061912fbaf673568938.png" alt="Filtración">
            <h3>Filtración</h3>
        </a>
        <a href="catalogo.php?categoria=Aditamentos" class="categoria-card">
            <span class="plus">+</span>
            <img src="images/architectural-blueprints.jpg" alt="Aditamentos">
            <h3>Aditamentos</h3>
        </a>
        <a href="catalogo.php?categoria=Mangueras" class="categoria-card">
            <span class="plus">+</span>
            <img src="images/1000072023_363388c333965dc6b7a4f11542885ccf-10_6_2025,_4_54_4...png" alt="Mangueras">
            <h3>Mangueras</h3>
        </a>
        <a href="catalogo.php?categoria=Hidráulico" class="categoria-card">
            <span class="plus">+</span>
            <img src="images/1000072027_1d617e617cfefd4fe1170b87a0fcd8d-10_6_2025,_4_34_1...png" alt="Hidráulicos">
            <h3>Componentes Hidráulicos</h3>
        </a>
        <a href="catalogo.php?categoria=Lubricantes" class="categoria-card">
            <span class="plus">+</span>
            <img src="images/1000072021_cd7a06a6617f6061912fbaf673568938.png" alt="Lubricantes">
            <h3>Lubricantes</h3>
        </a>
    </div>
</div>

<!-- MARCAS -->
<div class="seccion" id="marcas">
    <h2 class="seccion-titulo">Nuestras Marcas</h2>
    <div class="marcas-grid">
        <div class="marca-card">HITACHI</div>
        <div class="marca-card">CASE</div>
        <div class="marca-card">YANMAR</div>
        <div class="marca-card">OKADA</div>
        <div class="marca-card">ITR</div>
        <div class="marca-card">STAL</div>
        <div class="marca-card">DONALDSON</div>
        <div class="marca-card">NEXPRO</div>
        <div class="marca-card">ENI</div>
        <div class="marca-card">CATERPILLAR</div>
    </div>
</div>

<!-- FOOTER -->
<footer id="contacto">
    <div>
        <h4>🔧 Almacén PARMAP</h4>
        <p>Repuestos originales para maquinaria pesada. Calidad garantizada y envíos rápidos a todo el país.</p>
    </div>
    <div>
        <h4>Contacto</h4>
        <p>📧 info@parmap.com</p>
        <p>📱 +57 300 000 0000</p>
        <p>📍 Ibagué, Tolima</p>
        <p>🕐 Lunes a Sábado 8am - 6pm</p>
    </div>
    <div>
        <h4>Enlaces</h4>
        <a href="tienda.php">Inicio</a>
        <a href="catalogo.php">Catálogo</a>
        <a href="#marcas">Marcas</a>
        <a href="registro.php">Registrarse</a>
        <a href="index.php">Iniciar Sesión</a>
    </div>
</footer>
<div class="footer-bottom">© 2026 Almacén PARMAP - Todos los derechos reservados</div>

<script>
    function buscarProducto() {
        const texto = document.getElementById('buscar').value.toLowerCase();
        const tarjetas = document.querySelectorAll('.producto-card');
        tarjetas.forEach(t => {
            t.style.display = t.getAttribute('data-nombre').includes(texto) ? 'block' : 'none';
        });
    }
</script>

</body>
</html>