require('dotenv').config();
const express = require('express');
const cors = require('cors');
const { MongoClient, ObjectId } = require('mongodb');

const app = express();
app.use(cors());
app.use(express.json());

const client = new MongoClient(process.env.MONGO_URI, {
    tls: true,
    tlsAllowInvalidCertificates: true
});
let db;

async function conectar() {
    await client.connect();
    db = client.db('los_litros');
    console.log('✅ Conectado a MongoDB Atlas');
}

// ── USUARIOS ─────────────────────────────────────────────

// Obtener todos los usuarios
app.get('/usuarios', async (req, res) => {
    const usuarios = await db.collection('usuarios').find().toArray();
    res.json({ success: true, usuarios });
});

// Obtener un usuario por id
app.get('/usuarios/:id', async (req, res) => {
    const usuario = await db.collection('usuarios').findOne({ _id: new ObjectId(req.params.id) });
    if (!usuario) return res.json({ success: false, message: 'No encontrado' });
    res.json({ success: true, usuario });
});

// Crear usuario
app.post('/usuarios', async (req, res) => {
    const { username, password, rol, activo, mysql_id } = req.body;
    if (!username || !password || !rol) {
        return res.json({ success: false, message: 'Datos incompletos' });
    }
    const result = await db.collection('usuarios').insertOne({
        username,
        password,
        rol,
        activo: activo ?? true,
        mysql_id: mysql_id ?? null,
        fecha_registro: new Date()
    });
    res.json({ success: true, id: result.insertedId });
});

// Actualizar usuario
app.put('/usuarios/:id', async (req, res) => {
    const { username, rol, activo } = req.body;
    await db.collection('usuarios').updateOne(
        { _id: new ObjectId(req.params.id) },
        { $set: { username, rol, activo } }
    );
    res.json({ success: true });
});

// Eliminar por ObjectId
app.delete('/usuarios/:id', async (req, res) => {
    await db.collection('usuarios').deleteOne({ _id: new ObjectId(req.params.id) });
    res.json({ success: true });
});

// Eliminar por mysql_id
app.delete('/usuarios/mysql/:mysql_id', async (req, res) => {
    const mysql_id = parseInt(req.params.mysql_id);
    const result = await db.collection('usuarios').deleteOne({
        $or: [
            { mysql_id: mysql_id },
            { mysql_id: mysql_id.toString() }
        ]
    });
    res.json({ success: true, deleted: result.deletedCount });
});

// Eliminar por username
app.delete('/usuarios/username/:username', async (req, res) => {
    const result = await db.collection('usuarios').deleteOne({ username: req.params.username });
    res.json({ success: true, deleted: result.deletedCount });
});

// ── VENTAS ───────────────────────────────────────────────

// Obtener ventas por fecha
app.get('/ventas', async (req, res) => {
    const fecha = req.query.fecha ?? new Date().toISOString().split('T')[0];
    const inicio = new Date(fecha + 'T00:00:00.000Z');
    const fin    = new Date(fecha + 'T23:59:59.999Z');

    const ventas = await db.collection('ventas').find({
        fecha: { $gte: inicio, $lte: fin }
    }).toArray();

    res.json({ success: true, ventas });
});

// Registrar venta
app.post('/ventas', async (req, res) => {
    const { mesa_id, mesa_nombre, mesero, productos, total } = req.body;
    if (!mesa_id || !productos) {
        return res.json({ success: false, message: 'Datos incompletos' });
    }
    const result = await db.collection('ventas').insertOne({
        mesa_id,
        mesa_nombre,
        mesero,
        productos,
        total,
        fecha: new Date()
    });
    res.json({ success: true, id: result.insertedId });
});

// Total del día
app.get('/ventas/total', async (req, res) => {
    const fecha = req.query.fecha ?? new Date().toISOString().split('T')[0];
    const inicio = new Date(fecha + 'T00:00:00.000Z');
    const fin    = new Date(fecha + 'T23:59:59.999Z');

    const result = await db.collection('ventas').aggregate([
        { $match: { fecha: { $gte: inicio, $lte: fin } } },
        { $group: { _id: null, total: { $sum: '$total' } } }
    ]).toArray();

    res.json({ success: true, total: result[0]?.total ?? 0 });
});

// ── INICIAR ──────────────────────────────────────────────
const PORT = process.env.PORT || 3000;
conectar().then(() => {
    app.listen(PORT, () => console.log(`🚀 API corriendo en http://localhost:${PORT}`));
}).catch(err => {
    console.error('❌ Error conectando a MongoDB:', err);
});