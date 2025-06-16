// Mostrar el popup de carga
function mostrarPopupCarga() {
    const popup = document.getElementById('popup-carga');
    if (popup) {
        popup.style.display = 'flex';
    }
}
// Ocultar el popup de carga
function ocultarPopupCarga() {
    const popup = document.getElementById('popup-carga');
    if (popup) {
        popup.style.display = 'none';
    }
}
//funcion en caso de session acudacada
async function alerta_sesion() {
    Swal.fire({
        type: 'error',
        title: 'Error de Sesión',
        text: "Sesión Caducada, Por favor inicie sesión",
        confirmButtonClass: 'btn btn-confirm mt-2',
        footer: '',
        timer: 1000
    });
    location.replace(base_url + "login");
}
// cargar elementos de menu
async function cargar_institucion_menu(id_ies = 0) {
    const formData = new FormData();
    formData.append('sesion', session_session);
    formData.append('token', token_token);
    try {
        let respuesta = await fetch(base_url_server + 'src/control/Institucion.php?tipo=listar', {
            method: 'POST',
            mode: 'cors',
            cache: 'no-cache',
            body: formData
        });
        let json = await respuesta.json();
        if (json.status) {
            let datos = json.contenido;
            let contenido = '';
            let sede = '';
            datos.forEach(item => {
                if (id_ies == item.id) {
                    sede = item.nombre;
                }
                contenido += `<button href="javascript:void(0);" class="dropdown-item notify-item" onclick="actualizar_ies_menu(${item.id});">${item.nombre}</button>`;
            });
            document.getElementById('contenido_menu_ies').innerHTML = contenido;
            document.getElementById('menu_ies').innerHTML = sede;
        }
        //console.log(respuesta);
    } catch (e) {
        console.log("Error al cargar categorias" + e);
    }

}
async function cargar_datos_menu(sede) {
    cargar_institucion_menu(sede);
}
// actualizar elementos del menu
async function actualizar_ies_menu(id) {
    const formData = new FormData();
    formData.append('id_ies', id);
    try {
        let respuesta = await fetch(base_url + 'src/control/sesion_cliente.php?tipo=actualizar_ies_sesion', {
            method: 'POST',
            mode: 'cors',
            cache: 'no-cache',
            body: formData
        });
        let json = await respuesta.json();
        if (json.status) {
            location.reload();
        }
        //console.log(respuesta);
    } catch (e) {
        console.log("Error al cargar instituciones" + e);
    }
}
function generar_paginacion(total, cantidad_mostrar) {
    let actual = document.getElementById('pagina').value;
    let paginas = Math.ceil(total / cantidad_mostrar);
    let paginacion = '<li class="page-item';
    if (actual == 1) {
        paginacion += ' disabled';
    }
    paginacion += ' "><button class="page-link waves-effect" onclick="numero_pagina(1);">Inicio</button></li>';
    paginacion += '<li class="page-item ';
    if (actual == 1) {
        paginacion += ' disabled';
    }
    paginacion += '"><button class="page-link waves-effect" onclick="numero_pagina(' + (actual - 1) + ');">Anterior</button></li>';
    if (actual > 4) {
        var iin = (actual - 2);
    } else {
        var iin = 1;
    }
    for (let index = iin; index <= paginas; index++) {
        if ((paginas - 7) > index) {
            var n_n = iin + 5;
        }
        if (index == n_n) {
            var nn = actual + 1;
            paginacion += '<li class="page-item"><button class="page-link" onclick="numero_pagina(' + nn + ')">...</button></li>';
            index = paginas - 2;
        }
        paginacion += '<li class="page-item ';
        if (actual == index) {
            paginacion += "active";
        }
        paginacion += '" ><button class="page-link" onclick="numero_pagina(' + index + ');">' + index + '</button></li>';
    }
    paginacion += '<li class="page-item ';
    if (actual >= paginas) {
        paginacion += "disabled";
    }
    paginacion += '"><button class="page-link" onclick="numero_pagina(' + (parseInt(actual) + 1) + ');">Siguiente</button></li>';

    paginacion += '<li class="page-item ';
    if (actual >= paginas) {
        paginacion += "disabled";
    }
    paginacion += '"><button class="page-link" onclick="numero_pagina(' + paginas + ');">Final</button></li>';
    return paginacion;
}
function generar_texto_paginacion(total, cantidad_mostrar) {
    let actual = document.getElementById('pagina').value;
    let paginas = Math.ceil(total / cantidad_mostrar);
    let iniciar = (actual - 1) * cantidad_mostrar;
    if (actual < paginas) {

        var texto = '<label>Mostrando del ' + (parseInt(iniciar) + 1) + ' al ' + ((parseInt(iniciar) + 1) + 9) + ' de un total de ' + total + ' registros</label>';
    } else {
        var texto = '<label>Mostrando del ' + (parseInt(iniciar) + 1) + ' al ' + total + ' de un total de ' + total + ' registros</label>';
    }
    return texto;
}
// ---------------------------------------------  DATOS DE CARGA PARA FILTRO DE BUSQUEDA -----------------------------------------------
//cargar programas de estudio
function cargar_ambientes_filtro(datos, form = 'busqueda_tabla_ambiente', filtro = 'filtro_ambiente') {
    let ambiente_actual = document.getElementById(filtro).value;
    lista_ambiente = `<option value="0">TODOS</option>`;
    datos.forEach(ambiente => {
        pe_selected = "";
        if (ambiente.id == ambiente_actual) {
            pe_selected = "selected";
        }
        lista_ambiente += `<option value="${ambiente.id}" ${pe_selected}>${ambiente.detalle}</option>`;
    });
    document.getElementById(form).innerHTML = lista_ambiente;
}
//cargar programas de estudio
function cargar_sede_filtro(sedes) {
    let sede_actual = document.getElementById('sede_actual_filtro').value;
    lista_sede = `<option value="0">TODOS</option>`;
    sedes.forEach(sede => {
        sede_selected = "";
        if (sede.id == sede_actual) {
            sede_selected = "selected";
        }
        lista_sede += `<option value="${sede.id}" ${sede_selected}>${sede.nombre}</option>`;
    });
    document.getElementById('busqueda_tabla_sede').innerHTML = lista_sede;
}



// ------------------------------------------- FIN DE DATOS DE CARGA PARA FILTRO DE BUSQUEDA -----------------------------------------------
async function validar_datos_reset_password() {
    let id = document.getElementById('data').value;
    let token = document.getElementById('data2').value;
    let sesion = ''; // Asegúrate de obtener el valor correcto si es necesario

    const formData = new FormData();
    formData.append('id', id);
    formData.append('token', token);
    formData.append('sesion', sesion); // Añadir sesion al FormData

    try {
        let respuesta = await fetch(base_url_server + 'src/control/Usuario.php?tipo=validar_datos_reset_password', {
            method: 'POST',
            mode: 'cors',
            cache: 'no-cache',
            body: formData
        });
        let json = await respuesta.json();
        if (json.status == false) {
            Swal.fire({
                type: 'error',
                title: 'Error de Link',
                text: "Link caducado, Verifique su correo",
                confirmButtonClass: 'btn btn-confirm mt-2',
                footer: '',
                timer: 1000
            }).then(() => {
                // Redirección al login después de mostrar el mensaje de error
                //window.location.href = base_url + 'login';
            });

            let formulario = document.getElementById('frm_reset_password');
            formulario.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    Link caducado, verifique su correo.
                </div>
                <button onclick="window.location.href='${base_url}login'" class="btn btn-primary">
                    Volver al Login
                </button>
            `;
        }
    } catch (e) {
        console.log("Error al cargar" + e);
    }
}

async function actualizar_password() {
    let id = document.getElementById('data').value;
    let password = document.getElementById('password').value;
    let sesion = ''; // Asegúrate de obtener el valor correcto si es necesario
    let token = ''; // Asegúrate de obtener el valor correcto si es necesario

    const formData = new FormData();
    formData.append('id', id);
    formData.append('password', password);
    formData.append('sesion', sesion); // Añadir sesion al FormData
    formData.append('token', token); // Añadir token al FormData

    try {
        let respuesta = await fetch(base_url_server + 'src/control/Usuario.php?tipo=actualizar_password', {
            method: 'POST',
            mode: 'cors',
            cache: 'no-cache',
            body: formData
        });
        let json = await respuesta.json();
        if (json.status) {
            Swal.fire({
                type: 'success',
                title: 'Éxito',
                text: json.msg,
                footer: '',
                timer: 1500
            }).then(() => {
                // Redirección al login después de actualización exitosa
                window.location.href = base_url + 'login';
            });
        } else {
            Swal.fire({
                type: 'error',
                title: 'Error',
                text: json.msg,
                footer: '',
                timer: 1500
            });
        }
    } catch (e) {
        console.log("Error al actualizar la contraseña: " + e);
        Swal.fire({
            type: 'error',
            title: 'Error',
            text: 'Error de conexión al servidor',
            footer: '',
            timer: 1500
        });
    }
}

function validar_imputs_password() {
    let pass1 = document.getElementById('password').value.trim();
    let pass2 = document.getElementById('password1').value.trim();

    if (pass1 !== pass2) {
        Swal.fire({
            type: 'error',
            title: 'Contraseñas no coinciden',
            text: "Las contraseñas deben ser iguales",
            footer: '',
            timer: 1500
        });
        return false;
    }

    if (pass1.length < 8) {
        Swal.fire({
            type: 'error',
            title: 'Error',
            text: 'La contraseña debe tener al menos 8 caracteres',
            footer: '',
            timer: 1500
        });
        return false;
    }

    // Si todo está bien, actualizar contraseña
    actualizar_password();
    return true;
}





 //enviar informacion de password y id al controlador usuario
    // en el controlador recibir informacion y encriptar la nueva contraseña
    // guardar en base de datos y actualizar campo de reset_password= 0 y token_password= 'vacio'
    // notificar a usuario sobre el estado del proceso con alertas

     //TAREA ENVIAR INFORMACION DE PASSWORD Y ID AL CONTROLADOR USUARIO
 // RESIVIR INFORMACION Y ENCRIPTAR LA NUEVA CONTRASEÑA 
 // GUARDAR EN BASE DE DAROS Y ACTUALIZAR CAMPO DE RESET_PASSWORD = 0 Y TOKEN_PASSWORD = ''
 // NOTIFICAR A USUARIO SOBRE EL ESTADO DEL PROCESO CON ALERTA

        
  