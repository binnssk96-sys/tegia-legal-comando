/*
 * Formulario de diagnóstico — Tegia Legal.
 * Envía el formulario #form-diagnostico a /formulario/enviar.php y muestra el resultado
 * sin recargar la página. Si algo falla, el visitante conserva lo que escribió.
 *
 * Espera en el HTML: campos con id d-nombre, d-correo, d-tel, d-area, d-msg, casillas d-c1, d-c2, d-c3,
 * y opcionalmente el widget de Turnstile (<div class="cf-turnstile" data-sitekey="...">).
 */
(function () {
  'use strict';
  var VERSION_TEXTOS = 'casillas-2026-11-23';
  var URL_ENVIO = '/formulario/enviar.php';
  var CAMPOS = { nombre: 'd-nombre', correo: 'd-correo', telefono: 'd-tel', area: 'd-area', relato: 'd-msg',
    autoriza_atencion: 'd-c1', autoriza_correos: 'd-c2', autoriza_whatsapp: 'd-c3' };

  var form = document.getElementById('form-diagnostico');
  if (!form) return;
  var inicio = Date.now();

  // Campo trampa: invisible para personas, los bots suelen llenarlo.
  var trampa = document.createElement('div');
  trampa.setAttribute('aria-hidden', 'true');
  trampa.style.cssText = 'position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden';
  trampa.innerHTML = '<label>Sitio web <input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></label>';
  form.appendChild(trampa);

  var estado = document.createElement('div');
  estado.className = 'form-estado';
  estado.setAttribute('role', 'status');
  estado.setAttribute('aria-live', 'polite');
  estado.style.cssText = 'margin-top:12px;font-size:13px;line-height:1.5';
  form.appendChild(estado);

  function mostrar(texto, tipo) {
    estado.textContent = texto;
    estado.style.color = tipo === 'ok' ? '#1e4433' : '#a1261b';
  }

  function limpiarErrores() {
    Object.keys(CAMPOS).forEach(function (k) {
      var el = document.getElementById(CAMPOS[k]);
      if (!el) return;
      el.removeAttribute('aria-invalid');
      var msg = document.getElementById(CAMPOS[k] + '-error');
      if (msg) msg.remove();
    });
  }

  function marcarErrores(campos) {
    var primero = null;
    Object.keys(campos).forEach(function (k) {
      var el = document.getElementById(CAMPOS[k]);
      if (!el) return;
      el.setAttribute('aria-invalid', 'true');
      var msg = document.createElement('div');
      msg.id = CAMPOS[k] + '-error';
      msg.textContent = campos[k];
      msg.style.cssText = 'color:#a1261b;font-size:12px;margin-top:4px';
      (el.closest('.fld') || el.closest('label') || el.parentNode).appendChild(msg);
      el.setAttribute('aria-describedby', msg.id);
      if (!primero) primero = el;
    });
    if (primero) primero.focus();
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    limpiarErrores();
    var boton = form.querySelector('button[type=submit]');
    var textoBoton = boton.textContent;
    boton.disabled = true;
    boton.textContent = 'Enviando…';
    mostrar('', 'ok');

    var datos = new FormData();
    Object.keys(CAMPOS).forEach(function (k) {
      var el = document.getElementById(CAMPOS[k]);
      if (!el) return;
      if (el.type === 'checkbox') { if (el.checked) datos.append(k, '1'); }
      else datos.append(k, el.value);
    });
    datos.append('version_textos', VERSION_TEXTOS);
    datos.append('inicio', String(inicio));
    datos.append('sitio_web', form.querySelector('[name=sitio_web]').value);
    var ts = form.querySelector('[name="cf-turnstile-response"]');
    if (ts) datos.append('cf-turnstile-response', ts.value);

    fetch(URL_ENVIO, { method: 'POST', body: datos, credentials: 'same-origin' })
      .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
      .then(function (j) {
        if (j.ok) {
          form.reset();
          inicio = Date.now();
          var conCorreos = datos.get('autoriza_correos') === '1';
          mostrar('¡Recibimos tu solicitud! Un abogado te responderá en menos de 24 horas. Te enviamos un correo con el acuse.'
            + (conCorreos ? ' Revisa también el correo para confirmar tu suscripción.' : ''), 'ok');
        } else {
          if (j.campos) marcarErrores(j.campos);
          mostrar(j.mensaje || 'No pudimos enviar tu solicitud. Intenta de nuevo o escríbenos por WhatsApp al +57 304 683 0354.', 'error');
        }
        if (window.turnstile && ts) window.turnstile.reset();
      })
      .catch(function () {
        mostrar('No hay conexión. Revisa tu internet e intenta de nuevo, o escríbenos por WhatsApp al +57 304 683 0354.', 'error');
      })
      .then(function () {
        boton.disabled = false;
        boton.textContent = textoBoton;
      });
  });
})();
