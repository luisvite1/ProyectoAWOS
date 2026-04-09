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
    <title>Reportes - Los Litros</title>
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

        .container { width: 95%; max-width: 1100px; margin: 28px auto; }

        .filtro {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }
        .filtro label { font-size: 0.9rem; color: rgba(255,255,255,0.7); }
        .filtro input[type="date"] {
            padding: 10px 14px;
            background: #1a1a1a;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: white;
            font-size: 0.9rem;
        }
        .filtro input[type="date"]:focus { outline: none; border-color: #e63946; }

        .btn-consultar {
            background: #e63946;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-consultar:hover { filter: brightness(1.1); }

        .btn-pdf {
            background: #1a1a1a;
            color: #e63946;
            border: 1px solid #e63946;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-pdf:hover { background: #e63946; color: white; }

        .total-card {
            background: linear-gradient(135deg, #e63946, #c72d39);
            border-radius: 14px;
            padding: 28px;
            text-align: center;
            margin-bottom: 24px;
            box-shadow: 0 8px 20px rgba(230,57,70,0.3);
        }
        .total-card .label { font-size: 0.9rem; opacity: 0.85; margin-bottom: 8px; }
        .total-card .monto { font-size: 2.8rem; font-weight: 700; font-family: Poppins, sans-serif; }
        .total-card .fecha-label { font-size: 0.8rem; opacity: 0.7; margin-top: 6px; }

        .grid-reportes {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .reporte-card {
            background: #1a1a1a;
            border-radius: 14px;
            padding: 20px;
            border: 1px solid #2a2a2a;
        }
        .reporte-card h3 {
            margin: 0 0 16px;
            font-size: 0.95rem;
            font-family: Poppins, sans-serif;
            color: rgba(255,255,255,0.85);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .reporte-card h3 i { color: #e63946; }

        .reporte-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #2a2a2a;
            font-size: 0.88rem;
        }
        .reporte-item:last-child { border-bottom: none; }
        .reporte-item .nombre { color: rgba(255,255,255,0.85); }
        .reporte-item .valor { font-weight: 700; color: #e63946; }
        .reporte-item .cantidad { color: rgba(255,255,255,0.45); font-size: 0.8rem; }

        .barra-wrap { margin-top: 4px; }
        .barra {
            height: 4px;
            background: rgba(255,255,255,0.08);
            border-radius: 999px;
            overflow: hidden;
            margin-top: 4px;
        }
        .barra-fill {
            height: 100%;
            background: #e63946;
            border-radius: 999px;
            transition: width 0.5s ease;
        }

        .empty-state {
            text-align: center;
            color: rgba(255,255,255,0.35);
            padding: 20px 0;
            font-size: 0.88rem;
        }

        @media (max-width: 640px) {
            .grid-reportes { grid-template-columns: 1fr; }
            .total-card .monto { font-size: 2rem; }
        }
    </style>
</head>
<body>

<header class="topbar">
    <a href="admin.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
    <h1>Reportes de ventas</h1>
    <div style="width:32px"></div>
</header>

<div class="container">
    <div class="filtro">
        <label>Fecha:</label>
        <input type="date" id="inputFecha">
        <button class="btn-consultar" onclick="cargarReporte()">
            <i class="fas fa-search"></i> Consultar
        </button>
        <button class="btn-pdf" id="btnPdf" onclick="generarPDF()">
            <i class="fas fa-file-pdf"></i> Descargar PDF
        </button>
    </div>

    <div class="total-card">
        <div class="label">Total vendido</div>
        <div class="monto" id="totalDia">$0.00</div>
        <div class="fecha-label" id="fechaLabel">—</div>
    </div>

    <div class="grid-reportes">
        <div class="reporte-card">
            <h3><i class="fas fa-box"></i> Productos más vendidos</h3>
            <div id="listaProductos"><p class="empty-state">Selecciona una fecha</p></div>
        </div>

        <div class="reporte-card">
            <h3><i class="fas fa-chair"></i> Ventas por mesa</h3>
            <div id="listaMesas"><p class="empty-state">Selecciona una fecha</p></div>
        </div>

        <div class="reporte-card">
            <h3><i class="fas fa-user"></i> Ventas por mesero</h3>
            <div id="listaMeseros"><p class="empty-state">Selecciona una fecha</p></div>
        </div>
    </div>
</div>

<!-- Scripts al final -->
<script src="frontend/js/logo_base64.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
let reporteActual = null;

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('inputFecha').value = '<?php echo date("Y-m-d"); ?>';
    cargarReporte();
});

async function cargarReporte() {
    const fecha = document.getElementById('inputFecha').value;
    if (!fecha) return;

    const res  = await fetch(`backend/get_reporte.php?fecha=${fecha}`);
    const data = await res.json();

    if (!data.success) return;

    reporteActual = data;

    document.getElementById('btnPdf').style.display = 'flex';
    document.getElementById('totalDia').textContent = `$${parseFloat(data.total_dia).toFixed(2)}`;
    document.getElementById('fechaLabel').textContent = formatearFecha(fecha);

    renderLista('listaProductos', data.productos, p => `
        <div class="reporte-item">
            <div>
                <div class="nombre">${p.nombre}</div>
                <div class="cantidad">${p.cantidad_vendida} unidades</div>
                <div class="barra-wrap">
                    <div class="barra">
                        <div class="barra-fill" style="width:${pct(p.subtotal, data.total_dia)}%"></div>
                    </div>
                </div>
            </div>
            <div class="valor">$${parseFloat(p.subtotal).toFixed(2)}</div>
        </div>
    `);

    renderLista('listaMesas', data.mesas, m => `
        <div class="reporte-item">
            <div class="nombre">${m.mesa}</div>
            <div class="valor">$${parseFloat(m.total_mesa).toFixed(2)}</div>
        </div>
    `);

    renderLista('listaMeseros', data.meseros, m => `
        <div class="reporte-item">
            <div class="nombre">${m.mesero}</div>
            <div class="valor">$${parseFloat(m.total_mesero).toFixed(2)}</div>
        </div>
    `);
}

function generarPDF() {
    if (!reporteActual) return;

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const d = reporteActual;
    let y = 55;

    // Encabezado con logo
    doc.setFillColor(230, 57, 70);
    doc.rect(0, 0, 210, 40, 'F');
    doc.addImage(LOGO_BASE64, 'JPEG', 5, 3, 34, 34);
    doc.setTextColor(255, 255, 255);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(20);
    doc.text('Bar "Los Litros"', 45, 18);
    doc.setFontSize(11);
    doc.setFont('helvetica', 'normal');
    doc.text(`Reporte de ventas — ${formatearFecha(d.fecha)}`, 45, 30);

    doc.setTextColor(0, 0, 0);

    // Total del día
    doc.setFillColor(245, 245, 245);
    doc.roundedRect(14, y - 8, 182, 20, 3, 3, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(13);
    doc.text('Total del día:', 20, y + 5);
    doc.setTextColor(230, 57, 70);
    doc.text(`$${parseFloat(d.total_dia).toFixed(2)}`, 150, y + 5);
    doc.setTextColor(0, 0, 0);
    y += 30;

    if (d.productos.length > 0) {
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(12);
        doc.setTextColor(230, 57, 70);
        doc.text('Productos vendidos', 14, y);
        y += 6;
        doc.setDrawColor(230, 57, 70);
        doc.line(14, y, 196, y);
        y += 8;
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        d.productos.forEach(p => {
            doc.text(`${p.nombre}`, 18, y);
            doc.text(`${p.cantidad_vendida} uds`, 120, y);
            doc.setTextColor(230, 57, 70);
            doc.text(`$${parseFloat(p.subtotal).toFixed(2)}`, 165, y);
            doc.setTextColor(0, 0, 0);
            y += 8;
        });
        y += 6;
    }

    if (d.mesas.length > 0) {
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(12);
        doc.setTextColor(230, 57, 70);
        doc.text('Ventas por mesa', 14, y);
        y += 6;
        doc.setDrawColor(230, 57, 70);
        doc.line(14, y, 196, y);
        y += 8;
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        d.mesas.forEach(m => {
            doc.text(`Mesa: ${m.mesa}`, 18, y);
            doc.setTextColor(230, 57, 70);
            doc.text(`$${parseFloat(m.total_mesa).toFixed(2)}`, 165, y);
            doc.setTextColor(0, 0, 0);
            y += 8;
        });
        y += 6;
    }

    if (d.meseros.length > 0) {
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(12);
        doc.setTextColor(230, 57, 70);
        doc.text('Ventas por mesero', 14, y);
        y += 6;
        doc.setDrawColor(230, 57, 70);
        doc.line(14, y, 196, y);
        y += 8;
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        d.meseros.forEach(m => {
            doc.text(`${m.mesero}`, 18, y);
            doc.setTextColor(230, 57, 70);
            doc.text(`$${parseFloat(m.total_mesero).toFixed(2)}`, 165, y);
            doc.setTextColor(0, 0, 0);
            y += 8;
        });
    }

    doc.setFontSize(8);
    doc.setTextColor(150, 150, 150);
    doc.text(`Generado el ${new Date().toLocaleString('es-MX')}`, 14, 285);
    doc.save(`reporte_${d.fecha}.pdf`);
}

function renderLista(id, items, template) {
    const el = document.getElementById(id);
    if (!items || items.length === 0) {
        el.innerHTML = '<p class="empty-state">Sin datos para esta fecha</p>';
        return;
    }
    el.innerHTML = items.map(template).join('');
}

function pct(valor, total) {
    if (!total || total == 0) return 0;
    return Math.min((valor / total) * 100, 100).toFixed(1);
}

function formatearFecha(fecha) {
    const [y, m, d] = fecha.split('-');
    const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    return `${parseInt(d)} de ${meses[parseInt(m)-1]} de ${y}`;
}
</script>
</body>
</html>