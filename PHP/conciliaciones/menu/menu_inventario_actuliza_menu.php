<?php
sc_reset_menu_delete();
sc_reset_menu_disable();

if([desactivar_btn_volver] === 'SI'){
	sc_menu_delete(item_41);	
}

buscardatoslogin([usr_login]);
// Administracion Inicio
// Adminidtrativo - Contabilidad - Finanzas - Recursos Humanos
	if([permite_url_portal_administrativo] === 'NO'){
		sc_menu_disable(item_122);
	}
	if([permite_url_portal_contabilidad] === 'NO'){
		sc_menu_disable(item_123);
	}
	if([permite_url_portal_finanzas] === 'NO'){
		sc_menu_disable(item_124;
	}
	if([permite_url_portal_rrhh] === 'NO'){
		sc_menu_disable(item_125);
	}
// Administracion Fin

// Operaciones Inicio
// Dispositivos - Inventario - Mantenimiento - Pos - Tareas y Proyectos
	if([permite_url_portal_dispositos] === 'NO'){
		sc_menu_disable(item_131);
	}
	if([permite_url_portal_inventario] === 'NO'){
		sc_menu_disable(item_132);
	}
	if([permite_url_portal_mantenimiento] === 'NO'){
		sc_menu_disable(item_133);
	}
	if([permite_url_portal_puntodeventa] === 'NO'){
		sc_menu_disable(item_134;
	}
	if([permite_url_portal_proyectos] === 'NO'){
		sc_menu_disable(item_135);
	}
// Operaciones Fin

// Comercial Inicio
// Aliados - Atencion Comercial- Eventos - Patrocinantes - Vendedor
	if([permite_url_portal_aliado] === 'NO'){
		sc_menu_disable(item_136);
	}
	if([permite_url_portal_atencion_comercial] === 'NO'){
		sc_menu_disable(item_137);
	}
	if([permite_url_portal_eventos] === 'NO'){
		sc_menu_disable(item_138);
	}
	if([permite_url_portal_patrocinadores] === 'NO'){
		sc_menu_disable(item_139;
	}

	if([permite_url_portal_vendedor] === 'NO'){
		sc_menu_disable(item_140;
	}
// Comercial Fin

// Soporte y Herramientas - Inicio
// Gestion de Redes - Helpdesk - Herramientas - Soporte Tecnico
	if([permite_url_portal_gestionredes] === 'NO'){
		sc_menu_disable(item_141);
	}
	if([permite_url_portal_helpdesk] === 'NO'){
		sc_menu_disable(item_142);
	}
	if([permite_url_portal_herramientas] === 'NO'){
		sc_menu_disable(item_143);
	}
	if([permite_url_portal_soporte] === 'NO'){
		sc_menu_disable(item_144);
	}
// Soporte y Herramientas Fin

// Reportes - Inicio
// Reportes Empresa - Reportes Sucursal
	if([permite_url_portal_reportes_matriz] === 'NO'){
		sc_menu_disable(item_145);
	}
	if([permite_url_portal_reportes] === 'NO'){
		sc_menu_disable(item_146);
	}
// Reportes Fin

// Otros - Inicio
// Configucaion - Intranet
	if([permite_url_portal_configuracion] === 'NO'){
		sc_menu_disable(item_147);
	}
	if([permite_url_portal_intranet] === 'NO'){
		sc_menu_disable(item_148);
	}
// Otros Fin





?>