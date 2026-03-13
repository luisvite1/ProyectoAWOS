document.getElementById("loginForm").addEventListener("submit", async function(e){

e.preventDefault();

const username = document.getElementById("username").value;
const password = document.getElementById("password").value;

try{

const respuesta = await fetch("/Proyecto_AWOS/backend/login.php",{
method:"POST",
headers:{
"Content-Type":"application/json"
},
body:JSON.stringify({
username:username,
password:password
})
});

const datos = await respuesta.json();

if(datos.success){

localStorage.setItem("usuario", JSON.stringify(datos.usuario));

if(datos.usuario.rol_id == 1){

window.location.href = "admin.php";

}else{

window.location.href = "comandero.html";

}

}else{

alert(datos.message);

}

}catch(error){

console.error(error);
alert("Error con el servidor");

}

});