icarosoft_obtenervariablesglobales.php - obtiene variables para datos e imagen y logo empresa

  0| <?php
  1|
  2|     //
  3|     //.   fuctiones disponibles para las variables globales
  4|     /*
  5|     
  6|     
  7|     buscardatoslogin($slogin);
  8|     buscardatosempresa($sempresa);
  9|     buscardatossucursal($sucursal);
 10|     
 11|     
 12|     
 13|     
 14|     
 15|     */
 16| // inicio de function  buscardatoslogin  -----------------------------------------creada 07/08/202---------------------idalwin salas -------------
 17|     //-------------------la funcion buscardatoslogin($slogin);                para obenter datos del usuario igamen de avatar ----------------
 18|
 19| function buscardatoslogin($slogin){
 20|     
 21|     // toma valores del get en la pagina en el string faltaria colocarle alguna seguridad o token 
 22| //echo "comienza 1";
 23|
 24| $sql = "SELECT 
 25|         login,                  
 26|         name,                   
 27|         email,                 
 28|         cedula,                
 29|         active,                 
 30|         priv_admin,             
 31|         mfa,                    
 32|         codigo_nivel,          
 33|         codigo_empresa,         
 34|         codigo_sucursal,        
 35|         telefono,               
 36|         imagen,                 
 37|         ultima_sesion,         
 38|         ventas_sucursales,     
 39|         cargo,                 
 40|         ultimo_cambio_pswd,     
 41|         tiempo_revalidacion,    
 42|         url_portal_aliado,      
 43|         url_portal_cliente,     
 44|         url_portal_dispositos, 
 45|         url_portal_intranet,    
 46|         url_portal_rrhh,        
 47|         url_portal_teblero_gerencial,
 48|         url_portal_administrativo, 
 49|         url_portal_atencion_comercial, 
 50|         url_portal_contabilidad,
 51|         url_portal_helpdesk,    
 52|         url_portal_mantenimiento,
 53|         url_portal_soporte,     
 54|         url_portal_vendedor,    
 55|         url_portal_proyectos,   
 56|         url_portal_eventos,    
 57|         url_portal_finanzas,    
 58|         url_portal_tributario,  
 59|         url_portal_gestionredes,
 60|         url_portal_tickets,    
 61|         url_portal_inventario, 
 62|         url_portal_puntodeventa,
 63|         url_portal_reportes,    
 64|         url_portal_formularios,
 65|         url_portal_herramientas,
 66|         url_portal_reportes_matriz,
 67|         url_portal_apps_compartidas,
 68|         url_portal_configuracion,
 69|         url_portal_patrocinadores
 70|     FROM 
 71|         seguridad_users 
 72|     WHERE 
 73|         login = '$slogin'"; // Usamos addslashes por seguridad
 74|
 75|     
 76| sc_lookup(rs, $sql, "conn_example");
 77|     
 78| if(count({rs}) == 0){
 79|     //echo "comienza 2";
 80|     sc_log_add('login Fail', {lang_login_fail} . {login});
 81|     
 82|     sc_error_message({lang_error_login});
 83| }
 84| else if({rs[0][4]} == 'Y')
 85| {
 86| //    echo "comienza 3";
 87|     [usr_login]                                        = {rs[0][0]}; // 'login' está en el índice 0
 88|     [usr_name]                                        = {rs[0][1]}; // 'name' está en el índice 1
 89|     [usr_email]                                        = {rs[0][2]}; // 'email' está en el índice 2
 90|     [usr_priv_admin]                                 = {rs[0][5]}; // 'priv_admin' está en el índice 5
 91|     [usr_nivel]                                         = {rs[0][7]}; // 'codigo_nivel' está en el índice 7
 92|     $sempresa                                        = {rs[0][8]}; // 'codigo_empresa' está en el índice 8
 93|     $ssucursal                                        = {rs[0][9]}; // 'codigo_sucursal' está en el índice 9
 94|     $usr_foto                                        = {rs[0][11]}; // 'imagen' está en el índice 11
 95|     [remember_me]                                   = {remember_me};
 96|     [usr_cedula]                                     = {rs[0][3]};
 97|     [usr_active]                                     = {rs[0][4]};
 98|     [usr_mfa_secret]                                 = {rs[0][6]};
 99|     [usr_telefono]                                   = {rs[0][10]};
100|     [usr_ultima_sesion]                              = {rs[0][12]};
101|     [usr_ventas_sucursales]                          = {rs[0][13]};
102|     [usr_cargo]                                      = {rs[0][14]};
103|     [usr_ultimo_cambio_pswd]                         = {rs[0][15]};
104|     [usr_tiempo_revalidacion]                        = {rs[0][16]};
105|     [permite_url_portal_aliado]                     = {rs[0][17]};
106|     [permite_url_portal_cliente]                    = {rs[0][18]};
107|     [permite_url_portal_dispositos]                 = {rs[0][19]};
108|     [permite_url_portal_intranet]                   = {rs[0][20]};
109|     [permite_url_portal_rrhh]                       = {rs[0][21]};
110|     [permite_url_portal_teblero_gerencial]          = {rs[0][22]};
111|     [permite_url_portal_administrativo]             = {rs[0][23]};
112|     [permite_url_portal_atencion_comercial]         = {rs[0][24]};
113|     [permite_url_portal_contabilidad]               = {rs[0][25]};
114|     [permite_url_portal_helpdesk]                   = {rs[0][26]};
115|     [permite_url_portal_mantenimiento]              = {rs[0][27]};
116|     [permite_url_portal_soporte]                    = {rs[0][28]};
117|     [permite_url_portal_vendedor]                   = {rs[0][29]};
118|     [permite_url_portal_proyectos]                  = {rs[0][30]};
119|     [permite_url_portal_eventos]                    = {rs[0][31]};
120|     [permite_url_portal_finanzas]                   = {rs[0][32]};
121|     [permite_url_portal_tributario]                 = {rs[0][33]};
122|     [permite_url_portal_gestionredes]               = {rs[0][34]};
123|     [permite_url_portal_tickets]                    = {rs[0][35]};
124|     [permite_url_portal_inventario]                 = {rs[0][36]};
125|     [permite_url_portal_puntodeventa]               = {rs[0][37]};
126|     [permite_url_portal_reportes]                   = {rs[0][38]};
127|     [permite_url_portal_formularios]                = {rs[0][39]};
128|     [permite_url_portal_herramientas]                = {rs[0][40]};
129|     [permite_url_portal_reportes_matriz]            = {rs[0][41]};
130|     [permite_url_portal_apps_compartidas]            = {rs[0][42]};
131|     [permite_url_portal_configuracion]                = {rs[0][43]};
132|     [permite_url_portal_patrocinadores]                = {rs[0][44]};
133|     
134| /*
135|     if( [sett_enable_2fa] == 'Y' && !empty({rs[0][4]})){ sc_redir('app_control_2fa'); }
136|
137|     if(isset([sett_remember_me]) && [sett_remember_me] == 'Y'){
138|         remember_me_validate();
139|     }
140| */
141|     
142|         // buscar imagen ----------------------------------------------------------------------------
143|       // Convierte el contenido de la imagen a base64
144|          $usr_imagen = base64_encode($usr_foto);
145|     
146|
147|           if ($usr_imagen != "") {
148|         
149|         //echo "comienza empresa 3";
150|
151|         // Genera un nombre de archivo único para la imagen
152|         $nombre_archivo = 'foto_' . $slogin . '.png';
153|
154|         // Decodifica la imagen base64
155|         $imagen_decodificada = base64_decode($usr_imagen);
156|
157|         // Guarda la imagen decodificada en un archivo en el servidor
158|         file_put_contents('../_lib/img/' . $nombre_archivo, $imagen_decodificada);
159|         
160|
161|         // Asigna la ruta del archivo a una variable global para su uso en la aplicación
162|         sc_set_global($usr_imagen);
163|         [usr_imagen] = '../_lib/img/' . $nombre_archivo;
164|               
165|               //    file_put_contents('../_lib/tmp/'. [usr_login], [usr_picture]);
166|              //  [usr_picture]= '../_lib/tmp/'. [usr_login];
167|         
168|     } else
169|     {
170|      
171|     //    echo "comienza empresa 4";
172|
173|     // Si la imagen no es válida, usa una imagen predeterminada
174|         [usr_imagen] = "../_lib/img/gear_24.png"; // Esta imagen está dentro de _lib/img/
175|     }
176|     
177|     
178|     
179| }
180| else
181| {
182|     //echo "comienza 4";
183|     sc_error_message({lang_error_not_active});
184|     sc_error_exit();
185| }
186|                                          }    // final de function buscardatoslogin
187|
188| // ------------------------------------------------------comienza function buscardatosempresa idalwin salas 07/08/2024---------------------------------------------------------//
189|     //-------------------la function buscardatosempresa($sempresa);          para obtener los datos de la empresa general imagenes  ---------------------
190|
191|
192| function buscardatosempresa($sempresa){
193| //echo "comienza empresa 1";
194| // SQL para obtener la imagen desde la base de datos
195| $sql_logo = "SELECT codigo, 
196|                     descripcion,
197|                     numero_identificacion,
198|                     direccion,
199|                     telefono,
200|                     celular, 
201|                     imagen, 
202|                     pagina_web,
203|                     url_portal_cliente,
204|                     url_portal_aliado,
205|                     url_portal_atencioncomercial,
206|                     url_portal_administrativo,
207|                     url_portal_soporte,
208|                     url_portal_intranet,
209|                     url_portal_contabilidad,
210|                     url_portal_tablerogerencial,
211|                     url_portal_helpdesk,
212|                     url_portal_vendedor,
213|                     url_portal_rrhh,
214|                     url_portal_dispositivos,
215|                     url_portal_mantenimiento,
216|                     url_portal_eventos,
217|                     url_portal_inventario,
218|                     url_portal_puntodeventa,
219|                     url_portal_reportes,
220|                     url_portal_formularios,
221|                     url_portal_proyectos,
222|                     url_portal_finanzas,
223|                     url_portal_tributario,
224|                     url_portal_gestionredes,
225|                     url_portal_tickets,
226|                     url_portal_herramientas,
227|                     url_portal_reportes_matriz,
228|                     url_portal_apps_compartidas,
229|                     url_portal_configuracion,
230|                     url_portal_patrocinadores,
231|                     contribuyente_especial,
232|                     sucursal_principal,
233|                     token_facturacion
234|                     FROM 
235|                     configuracion_empresa 
236|                     WHERE codigo =  '$sempresa'";
237|
238| // Ejecuta la consulta
239| sc_lookup(ds_buscarempresa, $sql_logo, "conn_example");
240|
241| if (isset({ds_buscarempresa}) && count({ds_buscarempresa}) > 0) {
242|     
243|     //echo "comienza empresa 2";
244|
245|         [usr_empresa]                                   = {ds_buscarempresa[0][0]};
246|         [emp_nombre_empresa]                             = {ds_buscarempresa[0][1]};
247|         [emp_rif]                                     = {ds_buscarempresa[0][2]};
248|         [emp_direccion]                               = {ds_buscarempresa[0][3]};
249|         [emp_telefono]                                   = {ds_buscarempresa[0][4]};
250|         [emp_celular]                                 = {ds_buscarempresa[0][5]}; 
251|         $emp_imagen_contenido                         = {ds_buscarempresa[0][6]};
252|         [emp_pagina_web]                               = {ds_buscarempresa[0][7]};
253|         [url_portal_cliente]                             = {ds_buscarempresa[0][8]};
254|         [url_portal_aliado]                               = {ds_buscarempresa[0][9]};
255|         [url_portal_atencioncomercial]                = {ds_buscarempresa[0][10]};
256|         [url_portal_administrativo]                   = {ds_buscarempresa[0][11]};
257|         [url_portal_soporte]                           = {ds_buscarempresa[0][12]};
258|         [url_portal_intranet]                         = {ds_buscarempresa[0][13]};
259|         [url_portal_contabilidad]                     = {ds_buscarempresa[0][14]};
260|         [url_portal_tablerogerencial]                 = {ds_buscarempresa[0][15]};
261|         [url_portal_helpdesk]                          = {ds_buscarempresa[0][16]};
262|         [url_portal_vendedor]                         = {ds_buscarempresa[0][17]};
263|         [url_portal_rrhh]                              = {ds_buscarempresa[0][18]};
264|         [url_portal_dispositivos]                     = {ds_buscarempresa[0][19]};
265|         [url_portal_mantenimiento]                    = {ds_buscarempresa[0][20]};
266|         [url_portal_eventos]                          = {ds_buscarempresa[0][21]};
267|         [url_portal_inventario]                       = {ds_buscarempresa[0][22]};
268|         [url_portal_puntodeventa]                     = {ds_buscarempresa[0][23]};
269|         [url_portal_reportes]                         = {ds_buscarempresa[0][24]};
270|         [url_portal_formularios]                      = {ds_buscarempresa[0][25]};
271|         [url_portal_proyectos]                        = {ds_buscarempresa[0][26]};
272|         [url_portal_finanzas]                         = {ds_buscarempresa[0][27]};
273|         [url_portal_tributario]                       = {ds_buscarempresa[0][28]};
274|         [url_portal_gestionredes]                     = {ds_buscarempresa[0][29]};
275|         [url_portal_tickets]                         = {ds_buscarempresa[0][30]};
276|         [url_portal_herramientas]                      = {ds_buscarempresa[0][31]};
277|         [url_portal_reportes_matriz]                  = {ds_buscarempresa[0][32]};
278|         [url_portal_apps_compartidas]                  = {ds_buscarempresa[0][33]};
279|         [url_portal_configuracion]                  = {ds_buscarempresa[0][34]};
280|         [url_portal_patrocinadores]                  = {ds_buscarempresa[0][35]};
281|         [emp_contribuyente_especial]                  = {ds_buscarempresa[0][36]};
282|         [emp_sucursal_principal]                      = {ds_buscarempresa[0][37]};
283|         [emp_token_facturacion]                       = {ds_buscarempresa[0][38]};
284|     
285|
286|     
287|
288|
289|
290|
291|
292|
293|     
294|     
295|     // buscar imagen ----------------------------------------------------------------------------
296|       // Convierte el contenido de la imagen a base64
297|          $emp_logo = base64_encode($emp_imagen_contenido);
298|     
299|
300|           if ($emp_logo != "") {
301|         
302|         //echo "comienza empresa 3";
303|
304|         // Genera un nombre de archivo único para la imagen
305|         $nombre_archivo = 'logo_' . $sempresa . '.png';
306|
307|         // Decodifica la imagen base64
308|         $imagen_decodificada = base64_decode($emp_logo);
309|
310|         // Guarda la imagen decodificada en un archivo en el servidor
311|         file_put_contents('../_lib/img/' . $nombre_archivo, $imagen_decodificada);
312|         
313|
314|         // Asigna la ruta del archivo a una variable global para su uso en la aplicación
315|         sc_set_global($emp_logo);
316|         [emp_logo] = '../_lib/img/' . $nombre_archivo;
317|         
318|     } else
319|     {
320|      
321|     //    echo "comienza empresa 4";
322|
323|     // Si la imagen no es válida, usa una imagen predeterminada
324|         [emp_logo] = "../_lib/img/gear_24.png"; // Esta imagen está dentro de _lib/img/
325|     }
326|     
327|     
328|                          } // fin del if------------------------------
329|
330|
331| }// final de function buscardatosempresa
332|
333|
334| function buscardatossucursal($sucursal){
335|
336|
337| $sql_sucursal = "SELECT descripcion                    
338|                     FROM 
339|                     configuracion_sucursal 
340|                     WHERE codigo =  '$sucursal'";
341|
342| // Ejecuta la consulta
343| sc_lookup(ds_buscarsucursal, $sql_sucursal, "conn_example");
344|    
345| [emp_nombre_sucursal]                 = {ds_buscarsucursal[0][0]};
346|  
347|
348|
349| }
350|
351|
352|
353|
354|
355|     
356| ?>


