# Configuración de nginx

Copia versionada del virtual host de producción. El archivo real vive fuera del
repositorio, así que si se reinstala Laragon o alguien regenera el sitio, hay que
volver a dejarlo como está aquí.

- Copia de referencia: `control-contratista.conf`
- Ruta real en el servidor: `C:/laragon/etc/nginx/sites-enabled/control-contratista.conf`

## El error que ya nos costó una mañana

La línea que reescribe las rutas hacia Laravel **debe** ser exactamente esta:

```nginx
try_files $uri $uri/ /index.php?$query_string;
```

Laragon suele generarla así, y está mal:

```nginx
try_files $uri $uri/ /index.php?$is_args$args;
```

`$is_args` ya vale `?` cuando la URL trae parámetros, así que junto al `?` que
está escrito a mano quedan dos. El resultado es que **el nombre del primer
parámetro de cada URL llega a PHP con un `?` pegado**:

```
URL:      /empresas?buscar=TRANS&nit=804014130
Laravel:  {"?buscar": "TRANS", "nit": "804014130"}
```

Es un fallo silencioso y desconcertante, porque el resto de los parámetros llegan
bien. Los síntomas que vimos:

- El primer campo del filtro de empresas no filtraba, pero el segundo (NIT) sí.
- `?page=2` no cambiaba de página en ningún listado.
- No aparece ningún error en los logs: para la aplicación, el parámetro
  simplemente no fue enviado.

Si `$is_args$args` se usa **sin** el `?` delante también funciona
(`/index.php$is_args$args`), pero conviene quedarse con `?$query_string` porque
es la forma que documenta Laravel y no se puede romper por descuido.

## Cómo aplicarla

```powershell
Copy-Item deploy\nginx\control-contratista.conf `
    C:\laragon\etc\nginx\sites-enabled\control-contratista.conf -Force

cd C:\laragon\bin\nginx\nginx-1.27.3
.\nginx.exe -t -p C:\laragon\bin\nginx\nginx-1.27.3\ -c conf\nginx.conf
.\nginx.exe -s reload -p C:\laragon\bin\nginx\nginx-1.27.3\ -c conf\nginx.conf
```

Ajusta `listen`, `server_name` y `root` si el servidor cambia de puerto, de IP o
de carpeta. La versión de nginx del comando también puede cambiar al actualizar
Laragon.

## Cómo comprobar que quedó bien

Lo más rápido es desde la propia aplicación: entra a Empresas, escribe algo en el
campo **Buscar** y confirma que filtra. Después pasa a la página 2 del listado y
confirma que cambia de página. Si el primer parámetro se está perdiendo, ninguna
de las dos cosas funciona.

Para una comprobación directa, crea un archivo temporal
`routes/web.php` con una ruta de diagnóstico:

```php
Route::get('/_diag_query', fn (Illuminate\Http\Request $request) => response()->json([
    'query_string' => $request->server('QUERY_STRING'),
    'laravel_query' => $request->query(),
]));
```

Luego `php artisan route:cache` y visita `/_diag_query?a=1&b=2`. Debe responder
con `{"a":"1","b":"2"}`. Si responde `{"?a":"1","b":"2"}`, el `try_files` sigue
mal. **Borra la ruta y vuelve a cachear** cuando termines.
