<?php
session_start();
include 'backend/config.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol_id'] != 1) {
    header('Location: index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de productos</title>
    <link rel="stylesheet" href="frontend/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #0f0f0f; color: white; font-family: Inter, sans-serif; margin: 0; }

        .topbar {
            background: #e63946;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .topbar h1 { margin: 0; font-size: 1.2rem; font-family: Poppins, sans-serif; }
        .back-btn { color: white; text-decoration: none; font-size: 1.2rem; opacity: 0.85; }
        .back-btn:hover { opacity: 1; }
        .btn-nuevo {
            background: white;
            color: #e63946;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .btn-nuevo:hover { background: #f0f0f0; }

        .container { width: 95%; max-width: 1200px; margin: 28px auto; }

        /* BUSCADOR Y FILTRO */
        .toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .toolbar input, .toolbar select {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
            background: #1a1a1a;
            color: white;
            font-size: 0.9rem;
        }
        .toolbar input { flex: 1; min-width: 180px; }
        .toolbar input:focus, .toolbar select:focus { outline: none; border-color: #e63946; }

        /* TABLA */
        .tabla { width: 100%; border-collapse: collapse; background: #1a1a1a; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.35); }
        .tabla th, .tabla td { padding: 13px 16px; border-bottom: 1px solid #2a2a2a; text-align: left; font-size: 0.88rem; }
        .tabla th { background: #e63946; color: white; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .tabla tbody tr:hover { background: #222; }
        .tabla tbody tr:last-child td { border-bottom: none; }

        .stock-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .stock-ok   { background: rgba(59,214,113,0.15); color: #3bd671; }
        .stock-low  { background: rgba(255,165,0,0.15);  color: #ffa500; }
        .stock-zero { background: rgba(255,107,107,0.15); color: #ff6b6b; }

        .actions { display: flex; gap: 8px; }
        .btn-icon {
            width: 32px; height: 32px;
            border: none; border-radius: 7px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.82rem;
            transition: filter 0.2s;
        }
        .btn-icon:hover { filter: brightness(1.2); }
        .btn-edit   { background: #2563eb; color: white; }
        .btn-delete { background: #e63946; color: white; }

        /* MODAL */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.65); backdrop-filter: blur(4px);
            justify-content: center; align-items: center; z-index: 100; padding: 16px;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: #1a1a1a;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px; padding: 26px;
            width: 100%; max-width: 400px;
            animation: popIn 0.22s ease;
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.93); }
            to   { opacity: 1; transform: scale(1); }
        }
        .modal-box h3 { margin: 0 0 18px; font-family: Poppins, sans-serif; font-size: 1rem; color: white; }
        .form-group { margin-bottom: 13px; }
        .form-group label { display: block; font-size: 0.8rem; color: rgba(255,255,255,0.6); margin-bottom: 5px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px 13px;
            background: #2a2a2a; border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px; color: white; font-size: 0.9rem;
            box-sizing: border-box;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: #e63946;
        }
        .form-group textarea { resize: vertical; min-height: 70px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .modal-actions { display: flex; gap: 10px; margin-top: 6px; }
        .modal-actions button {
            flex: 1; padding: 11px; border: none; border-radius: 10px;
            font-weight: 700; font-size: 0.88rem; cursor: pointer; transition: filter 0.2s;
        }
        .modal-actions button:hover { filter: brightness(1.1); }
        .btn-cancelar  { background: #2a2a2a; color: rgba(255,255,255,0.6); }
        .btn-confirmar { background: #e63946; color: white; }

        /* MOBILE */
        @media (max-width: 640px) {
            .tabla thead { display: none; }
            .tabla, .tabla tbody { display: block; background: transparent; box-shadow: none; }
            .tabla tbody tr {
                display: flex; flex-wrap: wrap;
                background: #1a1a1a; border-radius: 12px;
                padding: 14px; margin-bottom: 10px;
                border: 1px solid #2a2a2a; gap: 4px;
                position: relative;
            }
            .tabla td { display: flex; flex-direction: column; border: none; padding: 2px 0; font-size: 0.88rem; }
            .tabla td:nth-child(1) { font-size: 0.72rem; color: rgba(255,255,255,0.35); width: 100%; }
            .tabla td:nth-child(2) { font-size: 1rem; font-weight: 700; width: 65%; }
            .tabla td:nth-child(3) { font-size: 0.8rem; color: rgba(255,255,255,0.55); width: 65%; }
            .tabla td:nth-child(4) { font-size: 0.85rem; font-weight: 600; width: 65%; }
            .tabla td:nth-child(5) { position: absolute; right: 14px; top: 14px; }
            .tabla td:nth-child(6) {
                width: 100%; flex-direction: row; justify-content: flex-end;
                padding-top: 10px; border-top: 1px solid #2a2a2a; margin-top: 6px;
            }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header class="topbar">
    <a href="admin.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
    <h1>Gestión de productos</h1>
    <button class="btn-nuevo" onclick="abrirModalNuevo()">
        <i class="fas fa-plus"></i> Nuevo
    </button>
</header>

<div class="container">
    <div class="toolbar">
        <input type="text" id="buscador" placeholder="Buscar producto...">
        <select id="filtroCat">
            <option value="">Todas las categorías</option>
        </select>
    </div>

    <table class="tabla">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tablaBody">
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:rgba(255,255,255,0.4)">Cargando...</td></tr>
        </tbody>
    </table>
</div>

<!-- MODAL CREAR / EDITAR -->
<div class="modal-overlay" id="modalProducto">
    <div class="modal-box">
        <h3 id="modalTitulo"><i class="fas fa-box"></i> Nuevo producto</h3>
        <input type="hidden" id="productoId">

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" id="inputNombre" placeholder="Ej: Corona, Tacos...">
        </div>
        <div class="form-group">
            <label>Categoría</label>
            <select id="inputCategoria"></select>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Precio ($)</label>
                <input type="number" id="inputPrecio" min="0" step="0.01" placeholder="0.00">
            </div>
            <div class="form-group">
                <label>Stock</label>
                <input type="number" id="inputStock" min="0" placeholder="0">
            </div>
        </div>
        <div class="form-group">
            <label>Descripción (opcional)</label>
            <textarea id="inputDescripcion" placeholder="Descripción breve..."></textarea>
        </div>

        <div class="modal-actions">
            <button class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-confirmar" onclick="guardarProducto()">Guardar</button>
        </div>
    </div>
</div>

<script>
let categorias = [];

document.addEventListener('DOMContentLoaded', () => {
    cargarCategorias().then(() => cargarProductos());
    document.getElementById('buscador').addEventListener('input', filtrar);
    document.getElementById('filtroCat').addEventListener('change', filtrar);
});

// ── CATEGORÍAS ──────────────────────────────────────────
async function cargarCategorias() {
    const res  = await fetch('backend/get_roles.php'); // reutiliza si tienes, si no usa el de abajo
    // Usamos endpoint propio
    const res2 = await fetch('backend/obtener_categorias.php');
    const data = await res2.json();
    categorias = data.categorias ?? [];

    const filtroCat   = document.getElementById('filtroCat');
    const inputCateg  = document.getElementById('inputCategoria');

    categorias.forEach(c => {
        filtroCat.innerHTML  += `<option value="${c.id}">${c.nombre}</option>`;
        inputCateg.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
    });
}

// ── PRODUCTOS ────────────────────────────────────────────
let todosProductos = [];

async function cargarProductos() {
    const res  = await fetch('backend/obtener_productos_admin.php');
    const data = await res.json();
    todosProductos = data.productos ?? [];
    renderTabla(todosProductos);
}

function renderTabla(productos) {
    const tbody = document.getElementById('tablaBody');

    if (productos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:rgba(255,255,255,0.4)">Sin productos</td></tr>';
        return;
    }

    tbody.innerHTML = productos.map(p => {
        const stock = parseInt(p.stock);
        const badgeClass = stock === 0 ? 'stock-zero' : stock <= 10 ? 'stock-low' : 'stock-ok';
        const badgeText  = stock === 0 ? 'Agotado' : stock <= 10 ? `${stock} bajo` : stock;

        return `
        <tr>
            <td>#${p.id_producto}</td>
            <td>${p.nombre}</td>
            <td>${p.categoria_nombre ?? '—'}</td>
            <td>$${parseFloat(p.precio).toFixed(2)}</td>
            <td><span class="stock-badge ${badgeClass}">${badgeText}</span></td>
            <td>
                <div class="actions">
                    <button class="btn-icon btn-edit" onclick="abrirModalEditar(${p.id_producto})">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="btn-icon btn-delete" onclick="eliminarProducto(${p.id_producto})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function filtrar() {
    const term = document.getElementById('buscador').value.toLowerCase();
    const cat  = document.getElementById('filtroCat').value;
    const filtrados = todosProductos.filter(p => {
        const coincideNombre = p.nombre.toLowerCase().includes(term);
        const coincideCat    = !cat || String(p.categoria_id) === cat;
        return coincideNombre && coincideCat;
    });
    renderTabla(filtrados);
}

// ── MODAL ────────────────────────────────────────────────
function abrirModalNuevo() {
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-plus"></i> Nuevo producto';
    document.getElementById('productoId').value    = '';
    document.getElementById('inputNombre').value   = '';
    document.getElementById('inputPrecio').value   = '';
    document.getElementById('inputStock').value    = '';
    document.getElementById('inputDescripcion').value = '';
    document.getElementById('inputCategoria').selectedIndex = 0;
    document.getElementById('modalProducto').classList.add('active');
}

function abrirModalEditar(id) {
    const p = todosProductos.find(x => x.id_producto == id);
    if (!p) return;

    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-pen"></i> Editar producto';
    document.getElementById('productoId').value    = p.id_producto;
    document.getElementById('inputNombre').value   = p.nombre;
    document.getElementById('inputPrecio').value   = p.precio;
    document.getElementById('inputStock').value    = p.stock;
    document.getElementById('inputDescripcion').value = p.descripcion ?? '';
    document.getElementById('inputCategoria').value   = p.categoria_id;
    document.getElementById('modalProducto').classList.add('active');
}

function cerrarModal() {
    document.getElementById('modalProducto').classList.remove('active');
}

async function guardarProducto() {
    const id          = document.getElementById('productoId').value;
    const nombre      = document.getElementById('inputNombre').value.trim();
    const precio      = document.getElementById('inputPrecio').value;
    const stock       = document.getElementById('inputStock').value;
    const descripcion = document.getElementById('inputDescripcion').value.trim();
    const categoria_id = document.getElementById('inputCategoria').value;

    if (!nombre || !precio || !stock || !categoria_id) {
        alert('Nombre, precio, stock y categoría son requeridos');
        return;
    }

    const endpoint = id ? 'backend/admin_editar_producto.php' : 'backend/admin_agregar_producto.php';

    const res  = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, nombre, precio, stock, descripcion, categoria_id })
    });
    const data = await res.json();

    if (data.success) {
        cerrarModal();
        cargarProductos();
    } else {
        alert(data.message || 'Error al guardar');
    }
}

async function eliminarProducto(id) {
    if (!confirm('¿Eliminar este producto?')) return;
    const res  = await fetch('backend/admin_eliminar_producto.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) cargarProductos();
    else alert(data.message || 'Error al eliminar');
}
</script>
</body>
</html>