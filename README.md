# Boxes Tracker (WordPress Plugin)

**Boxes Tracker** es un plugin personalizado para WordPress que permite a los usuarios consultar el estado de sus envíos de manera sencilla e integrada. Soporta múltiples transportadoras (Couriers) y renderiza el historial de seguimiento del paquete directamente en tu sitio web.

## 🚀 Características

- **Soporte Multi-Transportadora:** Detección automática del Courier basado en la longitud y estructura del número de guía. Soporta nativamente DHL, UPS, FEDEX y COORDINADORA.
- **Shortcode Sencillo:** Integración rápida en cualquier página o entrada usando `[boxes_tracker]`.
- **Integración SOAP Aislada:** Integración robusta y nativa (`SoapClient`) con el Web Service de Coordinadora para sortear bloqueos de seguridad y mantener la eficiencia.
- **Peticiones AJAX:** Búsqueda asíncrona sin recargar la página, ofreciendo una experiencia de usuario fluida.

## 📦 Instalación

1. Sube la carpeta del plugin `boxes-tracking-number` al directorio `/wp-content/plugins/` de tu instalación de WordPress.
2. Activa el plugin **Boxes Tracker** desde el menú "Plugins" en WordPress.
3. Dirígete a **Ajustes > Boxes Tracker** para configurar las credenciales.

## ⚙️ Configuración

Para que el plugin funcione correctamente, debes ingresar las credenciales de las APIs en la página de opciones del plugin (`Ajustes > Boxes Tracker`):

### 1. Configuración de la API (Global)
Esta API se utiliza para procesar envíos internacionales o de las transportadoras como UPS, DHL y FEDEX.
- **URL Base de la API:** Agrega la URL del endpoint sin parámetros (ej. `https://mi-endpoint-global.com/api`).

### 2. Configuración API SOAP Coordinadora
Debido a requerimientos específicos de red y seguridad, las consultas de Coordinadora (guías de 11 dígitos) se realizan mediante un Web Service propio y aislado de forma directa.
- **API Key:** Tu clave de integración brindada por Coordinadora.
- **Contraseña:** Tu contraseña asignada para el Web Service.
- **NIT:** El NIT de tu comercio registrado con la transportadora.

> ⚠️ **Nota de Seguridad para Coordinadora:** Si el sitio va a ser alojado en un servidor de producción o cambiar de IP, es **obligatorio** que la IP pública del servidor final se encuentre en la lista blanca (Whitelist) del Firewall (Cloudflare) de Coordinadora; de lo contrario, la conexión será rechazada con un Error 403 / Acceso Denegado.

## 🖥️ Uso en el sitio web

Para mostrar el formulario de consulta en tu página, utiliza el siguiente Shortcode en cualquier editor de texto o constructor visual (Elementor, Gutenberg, etc.):

```text
[boxes_tracker]
```

## 🛠️ Detalles Técnicos

- **PHP SoapClient:** El plugin utiliza la extensión nativa `SoapClient` de PHP para conectarse e interpretar el WSDL de Coordinadora (`https://ws.coordinadora.com/ags/1.5/server.php?wsdl`).
- **Assets Propios:** El shortcode encola de manera inteligente los archivos CSS y JS (`assets/css/boxes-tracker.css`, `assets/js/boxes-tracker.js`) **sólo** en las páginas donde el shortcode esté presente, ahorrando tiempos de carga globales en tu sitio.
- **Detección de Courier:** La función `detect_courier()` se encarga de analizar los patrones del string ingresado (ej. `1Z...` para UPS, 10 dígitos para DHL, 11 para Coordinadora, etc.) para priorizar la API adecuada.

## 📝 Estructura de Archivos Principal

```text
boxes-tracking-number/
├── boxes-tracker-plugin.php  # Archivo principal y lógica de clases
├── README.md                 # Documentación del plugin
├── assets/                   
│   ├── css/                  # Estilos del frontend del buscador
│   └── js/                   # Lógica AJAX y manipulador del formulario
└── templates/
    └── tracking-result.php   # Plantilla HTML de respuesta con la tabla de eventos
```
