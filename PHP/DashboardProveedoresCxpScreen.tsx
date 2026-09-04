import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { ActivityIndicator, KeyboardAvoidingView, Modal, Platform, Pressable, ScrollView, Text, TextInput, View, useWindowDimensions, type DimensionValue } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import { useProtectedResource } from '../../../hooks/useProtectedResource';
import { stitchColors } from '../../../theme/stitch';
import {
  type SucursalAsignada,
  fetchSucursalesAsignadasUsuarioPorEmpresa,
} from '../../../services/contextSwitch';
import {
  fetchValidateTokenClaims,
  getBranchCodeFromClaims,
  getCompanyCodeFromClaims,
} from '../../../services/moduleSession';
import { getAccessToken, getSeguridadUser } from '../../../utils/storage';
import {
  fetchDashboardProveedoresCxp,
  fetchProveedorByCodigo,
  fetchProveedorCompras,
  fetchProveedorCuentasPago,
  fetchProveedorEstadoCuenta,
  fetchProveedoresProductosPorCodigo,
  type ProveedorCompraItem,
  type ProveedorCuentaPago,
  type ProveedorDeudaTop,
  type ProveedorEstadoCuentaItem,
  type ProveedorFicha,
  type ProveedorProductoPorCodigo,
  type ProveedoresFacturaPendiente,
} from '../services/dashboardProveedoresCxpQueries';

type DashboardProveedoresCxpScreenProps = {
  onSessionExpired: () => void;
  onFallbackToWeb?: () => void;
};

function formatNumber(value: number): string {
  return new Intl.NumberFormat('es-DO').format(value || 0);
}

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('es-DO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value || 0);
}

function formatDate(value: string | number | null | undefined): string {
  if (value === null || value === undefined) return 'N/A';
  const raw = String(value).trim();
  if (!raw) return 'N/A';

  const toDisplay = (date: Date): string => {
    if (Number.isNaN(date.getTime()) || date.getFullYear() < 1990 || date.getFullYear() > 2100) {
      return 'N/A';
    }
    return `${pad2(date.getDate())}/${pad2(date.getMonth() + 1)}/${date.getFullYear()}`;
  };

  if (/^-?\d+(\.\d+)?$/.test(raw)) {
    const n = Number(raw);
    if (!Number.isFinite(n)) return 'N/A';
    const abs = Math.abs(n);
    if (abs < 1e9) return 'N/A';
    const parsed = new Date(abs < 1e12 ? n * 1000 : n);
    return toDisplay(parsed);
  }

  const iso = parseISODate(raw.slice(0, 10));
  if (iso) return toDisplay(iso);

  const typed = parseTypedDate(raw);
  if (typed) {
    const parsedTyped = parseISODate(typed);
    if (parsedTyped) return toDisplay(parsedTyped);
  }

  return toDisplay(new Date(raw));
}

function sucursalDescripcion(
  codigo: string | null | undefined,
  sucursales: SucursalAsignada[],
): string {
  const code = (codigo ?? '').trim();
  if (!code) return 'N/A';
  const found = sucursales.find(
    (item) => (item.codigo_sucursal ?? '').trim().toLowerCase() === code.toLowerCase(),
  );
  return found?.descripcion?.trim() || code;
}

function statusTone(status: string | null): { bg: string; text: string } {
  const normalized = (status || '').toUpperCase();
  if (normalized.includes('ANUL')) return { bg: '#fde8e8', text: '#b42318' };
  if (normalized.includes('ESPERA')) return { bg: '#fff4e5', text: '#b45309' };
  if (normalized.includes('INVENTARIO')) return { bg: '#e8f7ee', text: '#0f8a4b' };
  return { bg: stitchColors.surfaceContainer, text: stitchColors.onSurfaceVariant };
}

function kpiCardTheme(kind: 'deuda' | 'documentos' | 'proveedores'): { bg: string; value: string; icon: string } {
  if (kind === 'deuda') return { bg: '#eef4ff', value: '#1e3a8a', icon: '#2d4f93' };
  if (kind === 'proveedores') return { bg: '#eefdf4', value: '#166534', icon: '#15803d' };
  return { bg: '#f7f7ff', value: stitchColors.onBackground, icon: '#4c5bd4' };
}

const WEEK_DAYS = ['L', 'M', 'X', 'J', 'V', 'S', 'D'] as const;
const MONTHS_ES = [
  'enero',
  'febrero',
  'marzo',
  'abril',
  'mayo',
  'junio',
  'julio',
  'agosto',
  'septiembre',
  'octubre',
  'noviembre',
  'diciembre',
] as const;

function pad2(value: number): string {
  return String(value).padStart(2, '0');
}

function formatISODate(value: Date): string {
  return `${value.getFullYear()}-${pad2(value.getMonth() + 1)}-${pad2(value.getDate())}`;
}

function parseISODate(value: string): Date | null {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;
  const [yearRaw, monthRaw, dayRaw] = value.split('-');
  const year = Number(yearRaw);
  const month = Number(monthRaw);
  const day = Number(dayRaw);
  const parsed = new Date(year, month - 1, day);
  if (
    Number.isNaN(parsed.getTime())
    || parsed.getFullYear() !== year
    || parsed.getMonth() !== month - 1
    || parsed.getDate() !== day
  ) {
    return null;
  }
  return parsed;
}

function startOfMonthDate(value: Date): Date {
  return new Date(value.getFullYear(), value.getMonth(), 1);
}

function shiftMonthDate(value: Date, delta: number): Date {
  return new Date(value.getFullYear(), value.getMonth() + delta, 1);
}

function addDays(value: Date, days: number): Date {
  const next = new Date(value);
  next.setDate(next.getDate() + days);
  return next;
}

function formatDateReadable(isoDate: string): string {
  const parsed = parseISODate(isoDate);
  if (!parsed) return '';
  return `${pad2(parsed.getDate())}/${pad2(parsed.getMonth() + 1)}/${parsed.getFullYear()}`;
}

function formatDateShort(isoDate: string): string {
  const parsed = parseISODate(isoDate);
  if (!parsed) return '';
  return `${pad2(parsed.getDate())}/${pad2(parsed.getMonth() + 1)}`;
}

function formatDateInputMask(raw: string): string {
  const digits = raw.replace(/\D/g, '').slice(0, 8);
  if (digits.length <= 2) return digits;
  if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
  return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
}

function parseTypedDate(value: string): string | null {
  const trimmed = value.trim();
  if (!trimmed) return null;

  const isoParsed = parseISODate(trimmed);
  if (isoParsed) return formatISODate(isoParsed);

  const match = trimmed.match(/^(\d{1,2})[/\-.](\d{1,2})[/\-.](\d{4})$/);
  if (!match) return null;

  const day = Number(match[1]);
  const month = Number(match[2]);
  const year = Number(match[3]);
  const parsed = new Date(year, month - 1, day);
  if (
    Number.isNaN(parsed.getTime())
    || parsed.getFullYear() !== year
    || parsed.getMonth() !== month - 1
    || parsed.getDate() !== day
  ) {
    return null;
  }

  return formatISODate(parsed);
}

function normalizeDateRange(from: string, to: string): { from: string; to: string } {
  const start = from.trim();
  const end = to.trim();
  if (start && end && start > end) {
    return { from: end, to: start };
  }
  return { from: start, to: end || start };
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null;
}

function SectionPagination({
  page,
  totalPages,
  totalItems,
  label,
  onPrev,
  onNext,
}: {
  page: number;
  totalPages: number;
  totalItems: number;
  label: string;
  onPrev: () => void;
  onNext: () => void;
}) {
  if (totalItems <= 0) return null;
  return (
    <View className="mt-2 flex-row items-center justify-between">
      <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
        Página {page} de {totalPages} · {totalItems} {label}
      </Text>
      <View className="flex-row" style={{ gap: 8 }}>
        <Pressable
          disabled={page <= 1}
          className="rounded-xl px-3 py-2"
          style={{
            backgroundColor: page <= 1 ? stitchColors.surfaceContainer : stitchColors.surface,
            borderWidth: 1,
            borderColor: stitchColors.outlineVariant,
          }}
          onPress={onPrev}
        >
          <Text
            className="text-xs font-semibold"
            style={{ color: page <= 1 ? stitchColors.onSurfaceVariant : stitchColors.onSurface }}
          >
            Anterior
          </Text>
        </Pressable>
        <Pressable
          disabled={page >= totalPages}
          className="rounded-xl px-3 py-2"
          style={{ backgroundColor: page >= totalPages ? stitchColors.surfaceContainer : stitchColors.primary }}
          onPress={onNext}
        >
          <Text
            className="text-xs font-semibold"
            style={{ color: page >= totalPages ? stitchColors.onSurfaceVariant : stitchColors.onPrimary }}
          >
            Siguiente
          </Text>
        </Pressable>
      </View>
    </View>
  );
}

function TopDeudaList({
  items,
  page,
  totalPages,
  totalItems,
  onPrevPage,
  onNextPage,
  listMaxHeight,
  panelHeight,
  onOpenProveedor,
}: {
  items: ProveedorDeudaTop[];
  page: number;
  totalPages: number;
  totalItems: number;
  onPrevPage: () => void;
  onNextPage: () => void;
  listMaxHeight?: number;
  panelHeight?: number;
  onOpenProveedor?: (codProveedor: string) => void;
}) {
  const safeItems = items.filter((item): item is ProveedorDeudaTop => isRecord(item));
  const maxDeuda = safeItems.reduce((acc, item) => Math.max(acc, item.total_deuda || 0), 0);

  return (
    <View
      className={`${panelHeight ? '' : 'mb-3 '}rounded-2xl border px-4 py-3`}
      style={{
        borderColor: stitchColors.outlineVariant,
        backgroundColor: stitchColors.surface,
        ...(panelHeight ? { height: panelHeight } : null),
      }}
    >
      <Text className="text-xs font-semibold uppercase" style={{ color: stitchColors.secondary, letterSpacing: 0.4 }}>
        Top proveedores con deuda
      </Text>
      {safeItems.length === 0 ? (
        <View className="mt-2" style={panelHeight ? { flex: 1, justifyContent: 'center' } : undefined}>
          <Text className="text-sm" style={{ color: stitchColors.onSurfaceVariant }}>
            Sin datos.
          </Text>
        </View>
      ) : (
        <ScrollView
          className="mt-2"
          style={
            panelHeight
              ? { flex: 1, minHeight: 0 }
              : listMaxHeight
                ? { maxHeight: listMaxHeight }
                : undefined
          }
          nestedScrollEnabled
          showsVerticalScrollIndicator={false}
        >
          <View style={{ gap: 8, paddingRight: 2 }}>
            {safeItems.map((item, index) => {
              const width: DimensionValue = maxDeuda > 0
                ? `${Math.max(10, Math.round(((item.total_deuda || 0) / maxDeuda) * 100))}%`
                : '0%';
              const cod = (item.cod_proveedor || '').trim();
              const canOpen = Boolean(onOpenProveedor && cod);
              return (
                <View key={`top-deuda-${item.cod_proveedor || index}`} className="rounded-xl border px-3 py-2.5" style={{ borderColor: stitchColors.outlineVariant }}>
                  <View className="flex-row items-center justify-between">
                    <Pressable
                      disabled={!canOpen}
                      onPress={() => canOpen && onOpenProveedor?.(cod)}
                      style={{ flex: 1 }}
                    >
                      <Text className="flex-1 text-sm font-semibold" style={{ color: canOpen ? stitchColors.primary : stitchColors.onBackground }}>
                        {item.nombre_proveedor || 'Proveedor sin nombre'}
                      </Text>
                    </Pressable>
                    <View className="rounded-full px-2 py-1" style={{ backgroundColor: `${stitchColors.primary}15` }}>
                      <Text className="text-[10px] font-bold" style={{ color: stitchColors.primary }}>
                        #{index + 1}
                      </Text>
                    </View>
                  </View>
                  <Text className="mt-1 text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                    Cod: {item.cod_proveedor || 'N/A'} · Docs: {formatNumber(item.documentos_pendientes)} · Vencimiento: {formatDate(item.proximo_vencimiento)}
                  </Text>
                  <View className="mt-2 h-2 overflow-hidden rounded-full" style={{ backgroundColor: stitchColors.surfaceContainer }}>
                    <View className="h-full rounded-full" style={{ width, backgroundColor: stitchColors.secondary }} />
                  </View>
                  <Text className="mt-1.5 text-sm font-semibold" style={{ color: stitchColors.secondary }}>
                    {formatCurrency(item.total_deuda)}
                  </Text>
                </View>
              );
            })}
          </View>
        </ScrollView>
      )}
      <SectionPagination
        page={page}
        totalPages={totalPages}
        totalItems={totalItems}
        label="proveedores"
        onPrev={onPrevPage}
        onNext={onNextPage}
      />
    </View>
  );
}

const SUCURSAL_CHART_COLORS = [
  '#103f8a', '#0b7285', '#0f766e', '#2d4f93', '#15803d',
  '#b45309', '#b42318', '#7c3aed', '#be185d', '#4c5bd4',
  '#0891b2', '#e8590c', '#c2255c', '#5b21b6', '#86198f',
];

function DeudaPorSucursalChart({ data }: { data: { codigo: string; descripcion: string; total: number }[] }) {
  const total = data.reduce((acc, item) => acc + (item.total || 0), 0);
  if (total <= 0 || data.length === 0) return null;

  let acumulado = 0;
  const segmentos = data.map((item, index) => {
    const porcion = total > 0 ? (item.total / total) * 100 : 0;
    const inicio = acumulado;
    acumulado += porcion;
    const color = SUCURSAL_CHART_COLORS[index % SUCURSAL_CHART_COLORS.length];
    return { ...item, porcion, inicio, fin: acumulado, color };
  });

  const conicGradient = segmentos
    .map((s) => `${s.color} ${s.inicio}% ${s.fin}%`)
    .join(', ');

  return (
    <View className="mb-2 rounded-2xl border px-4 py-3" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}>
      <Text className="text-xs font-semibold" style={{ color: stitchColors.secondary }}>
        Deuda por sucursal
      </Text>
      <View className="mt-3 flex-row items-center" style={{ gap: 16 }}>
        <View
          style={{
            width: 160,
            height: 160,
            borderRadius: 80,
            background: `conic-gradient(${conicGradient})`,
          } as Record<string, unknown>}
        />
        <View className="flex-1" style={{ gap: 6 }}>
          {segmentos.map((s) => (
            <View key={`sucursal-chart-${s.codigo}`} className="flex-row items-center" style={{ gap: 8 }}>
              <View style={{ width: 12, height: 12, borderRadius: 6, backgroundColor: s.color }} />
              <Text className="flex-1 text-[11px]" numberOfLines={1} style={{ color: stitchColors.onSurface }}>
                {s.descripcion}
              </Text>
              <Text className="text-[11px] font-semibold" style={{ color: stitchColors.onSurfaceVariant }}>
                {formatCurrency(s.total)}
              </Text>
            </View>
          ))}
        </View>
      </View>
    </View>
  );
}

function PendientesTable({
  items,
  page,
  totalPages,
  totalItems,
  onPrevPage,
  onNextPage,
  listMaxHeight,
  panelHeight,
  sucursales,
  onOpenProveedor,
}: {
  items: ProveedoresFacturaPendiente[];
  page: number;
  totalPages: number;
  totalItems: number;
  onPrevPage: () => void;
  onNextPage: () => void;
  listMaxHeight?: number;
  panelHeight?: number;
  sucursales: SucursalAsignada[];
  onOpenProveedor?: (codProveedor: string) => void;
}) {
  const safeItems = items.filter((item): item is ProveedoresFacturaPendiente => isRecord(item));

  return (
    <View
      className={`${panelHeight ? '' : 'mb-2 '}rounded-2xl border px-4 py-3`}
      style={{
        borderColor: stitchColors.outlineVariant,
        backgroundColor: stitchColors.surface,
        ...(panelHeight ? { height: panelHeight } : null),
      }}
    >
      <Text className="text-xs font-semibold" style={{ color: stitchColors.secondary }}>
        Facturas pendientes
      </Text>
      {safeItems.length === 0 ? (
        <View className="mt-2" style={panelHeight ? { flex: 1, justifyContent: 'center' } : undefined}>
          <Text className="text-sm" style={{ color: stitchColors.onSurfaceVariant }}>
            Sin facturas pendientes para los filtros actuales.
          </Text>
        </View>
      ) : (
        <ScrollView
          className="mt-2"
          style={
            panelHeight
              ? { flex: 1, minHeight: 0 }
              : listMaxHeight
                ? { maxHeight: listMaxHeight }
                : undefined
          }
          nestedScrollEnabled
          showsVerticalScrollIndicator={false}
        >
          <View style={{ gap: 8, paddingRight: 2 }}>
            {safeItems.map((item, index) => {
              const tone = statusTone(item.status_compra || item.estatus);
              const vencido = (item.dias_vencidos || 0) > 0;
              const cod = (item.cod_proveedor || '').trim();
              const canOpen = Boolean(onOpenProveedor && cod);
              return (
                <View key={`pendiente-${item.id_cxp_documentos ?? item.numero_documento ?? index}`} className="rounded-xl border px-3 py-2.5" style={{ borderColor: stitchColors.outlineVariant }}>
                  <View className="flex-row items-center justify-between" style={{ gap: 8 }}>
                    <Pressable
                      disabled={!canOpen}
                      onPress={() => canOpen && onOpenProveedor?.(cod)}
                      style={{ flex: 1 }}
                    >
                      <Text className="flex-1 text-xs font-semibold" style={{ color: canOpen ? stitchColors.primary : stitchColors.onBackground }}>
                        {item.numero_documento || 'Sin documento'} - {item.nombre_proveedor || 'Sin proveedor'}
                      </Text>
                    </Pressable>
                    <View className="rounded-full px-2 py-1" style={{ backgroundColor: tone.bg }}>
                      <Text className="text-[10px] font-bold" style={{ color: tone.text }}>
                        {(item.status_compra || item.estatus || 'N/A').slice(0, 24)}
                      </Text>
                    </View>
                  </View>
                  <Text className="mt-1 text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                    Fecha Factura: {formatDate(item.fecha_emision)} · Vencimiento: {formatDate(item.fecha_vencimiento)}
                  </Text>
                  <Text className="mt-0.5 text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                    Sucursal: {sucursalDescripcion(item.sucursal, sucursales)}
                  </Text>
                  <View className="mt-1 flex-row items-center justify-between">
                    <Text className="text-[11px] font-semibold" style={{ color: vencido ? '#b42318' : stitchColors.onSurfaceVariant }}>
                      Días vencidos: {formatNumber(item.dias_vencidos)}
                    </Text>
                    <Text className="text-sm font-semibold" style={{ color: stitchColors.secondary }}>
                      {formatCurrency(item.saldo)}
                    </Text>
                  </View>
                </View>
              );
            })}
          </View>
        </ScrollView>
      )}
      <SectionPagination
        page={page}
        totalPages={totalPages}
        totalItems={totalItems}
        label="facturas"
        onPrev={onPrevPage}
        onNext={onNextPage}
      />
    </View>
  );
}

function ProveedorFichaView({
  codigo,
  detalle,
  cuentas,
  loading,
  error,
  sucursales,
  onBack,
  onRetry,
}: {
  codigo: string;
  detalle: ProveedorFicha | null;
  cuentas: ProveedorCuentaPago[];
  loading: boolean;
  error: string;
  sucursales: SucursalAsignada[];
  onBack: () => void;
  onRetry: () => void;
}) {
  const nombre = detalle?.nombre_proveedor?.trim() || `Proveedor ${codigo}`;
  const tone = statusTone(detalle?.status ?? null);
  const { width: fichaWidth } = useWindowDimensions();
  const fichaIsDesktop = fichaWidth >= 1200;
  const [seccionActiva, setSeccionActiva] = useState<'estado' | 'compras' | 'pagos' | 'productos'>('estado');
  const [showSeccionSelector, setShowSeccionSelector] = useState(false);
  const [estadoCuenta, setEstadoCuenta] = useState<ProveedorEstadoCuentaItem[]>([]);
  const [loadingEstadoCuenta, setLoadingEstadoCuenta] = useState(false);
  const [errorEstadoCuenta, setErrorEstadoCuenta] = useState('');
  const [productosProveedor, setProductosProveedor] = useState<ProveedorProductoPorCodigo[]>([]);
  const [loadingProductos, setLoadingProductos] = useState(false);
  const [errorProductos, setErrorProductos] = useState('');
  const [comprasProveedor, setComprasProveedor] = useState<ProveedorCompraItem[]>([]);
  const [loadingCompras, setLoadingCompras] = useState(false);
  const [errorCompras, setErrorCompras] = useState('');
  const seccionesOpciones = [
    { value: 'estado', label: 'Estado de Cuenta' },
    { value: 'compras', label: 'Compras' },
    { value: 'pagos', label: 'Pagos Realizados' },
    { value: 'productos', label: 'Productos del Proveedor' },
  ] as const;

  useEffect(() => {
    if (seccionActiva !== 'estado') {
      return;
    }
    const idProveedor = detalle?.id_proveedor;
    if (idProveedor === null || idProveedor === undefined || idProveedor === '') {
      setEstadoCuenta([]);
      setErrorEstadoCuenta('El proveedor no tiene id_proveedor para consultar el estado de cuenta.');
      return;
    }

    let cancelled = false;
    setLoadingEstadoCuenta(true);
    setErrorEstadoCuenta('');

    void (async () => {
      try {
        const accessToken = (await getAccessToken())?.trim() ?? '';
        if (!accessToken) {
          if (!cancelled) {
            setEstadoCuenta([]);
            setErrorEstadoCuenta('No hay sesión activa para consultar el estado de cuenta.');
            setLoadingEstadoCuenta(false);
          }
          return;
        }

        const result = await fetchProveedorEstadoCuenta(accessToken, idProveedor, [
          'FACTURADO',
          'EN INVENTARIO',
          'EN ESPERA',
        ]);

        if (cancelled) {
          return;
        }
        setEstadoCuenta(result);
      } catch (err) {
        if (cancelled) {
          return;
        }
        const message = err instanceof Error ? err.message : 'No se pudo consultar el estado de cuenta.';
        setErrorEstadoCuenta(message);
        setEstadoCuenta([]);
      } finally {
        if (!cancelled) {
          setLoadingEstadoCuenta(false);
        }
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [seccionActiva, detalle?.id_proveedor]);

  const totalesEstadoCuenta = useMemo(() => {
    const saldoCredito = estadoCuenta.reduce((acc, row) => acc + (row.saldo_credito ?? 0), 0);
    const saldoDebito = estadoCuenta.reduce((acc, row) => acc + (row.saldo_debito ?? 0), 0);
    const saldoNeto = saldoDebito - saldoCredito;
    return { saldoCredito, saldoDebito, saldoNeto };
  }, [estadoCuenta]);

  useEffect(() => {
    if (seccionActiva !== 'productos') {
      return;
    }
    const codProveedor = codigo.trim();
    if (!codProveedor) {
      setProductosProveedor([]);
      setErrorProductos('El proveedor no tiene código para consultar productos.');
      return;
    }

    let cancelled = false;
    setLoadingProductos(true);
    setErrorProductos('');

    void (async () => {
      try {
        const accessToken = (await getAccessToken())?.trim() ?? '';
        if (!accessToken) {
          if (!cancelled) {
            setProductosProveedor([]);
            setErrorProductos('No hay sesión activa para consultar los productos.');
            setLoadingProductos(false);
          }
          return;
        }

        const result = await fetchProveedoresProductosPorCodigo(accessToken, {
          codProveedor,
          codigoProducto: null,
          sucursal: null,
          limit: 100,
          offset: 0,
        });

        if (cancelled) {
          return;
        }
        setProductosProveedor(result);
      } catch (err) {
        if (cancelled) {
          return;
        }
        const message = err instanceof Error ? err.message : 'No se pudieron consultar los productos del proveedor.';
        setErrorProductos(message);
        setProductosProveedor([]);
      } finally {
        if (!cancelled) {
          setLoadingProductos(false);
        }
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [seccionActiva, codigo]);

  const totalesProductos = useMemo(() => {
    const montoTotal = productosProveedor.reduce((acc, row) => acc + (row.monto_total ?? 0), 0);
    const cantidadTotal = productosProveedor.reduce((acc, row) => acc + (row.cantidad_total ?? 0), 0);
    const comprasTotales = productosProveedor.reduce((acc, row) => acc + (row.compras_count ?? 0), 0);
    return { montoTotal, cantidadTotal, comprasTotales };
  }, [productosProveedor]);

  useEffect(() => {
    if (seccionActiva !== 'compras') {
      return;
    }
    const idProveedor = detalle?.id_proveedor;
    if (idProveedor === null || idProveedor === undefined || idProveedor === '') {
      setComprasProveedor([]);
      setErrorCompras('El proveedor no tiene id_proveedor para consultar las compras.');
      return;
    }

    let cancelled = false;
    setLoadingCompras(true);
    setErrorCompras('');

    void (async () => {
      try {
        const accessToken = (await getAccessToken())?.trim() ?? '';
        if (!accessToken) {
          if (!cancelled) {
            setComprasProveedor([]);
            setErrorCompras('No hay sesión activa para consultar las compras.');
            setLoadingCompras(false);
          }
          return;
        }

        const result = await fetchProveedorCompras(accessToken, idProveedor);

        if (cancelled) {
          return;
        }
        setComprasProveedor(result);
      } catch (err) {
        if (cancelled) {
          return;
        }
        const message = err instanceof Error ? err.message : 'No se pudieron consultar las compras del proveedor.';
        setErrorCompras(message);
        setComprasProveedor([]);
      } finally {
        if (!cancelled) {
          setLoadingCompras(false);
        }
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [seccionActiva, detalle?.id_proveedor]);

  const totalesCompras = useMemo(() => {
    const totalBs = comprasProveedor.reduce((acc, row) => acc + (row.total_bs ?? 0), 0);
    const totalUsd = comprasProveedor.reduce((acc, row) => acc + (row.total_usd ?? 0), 0);
    return { totalBs, totalUsd };
  }, [comprasProveedor]);


  const dataRows = useMemo(() => {
    if (!detalle) return [];
    return [
      { label: 'RIF', value: [detalle.tipo_rif, detalle.rif_proveedor].filter(Boolean).join('') || 'N/A' },
      { label: 'Teléfono fijo', value: detalle.telefono_fijo || 'N/A' },
      { label: 'Teléfono móvil', value: detalle.telefono_movil || 'N/A' },
      { label: 'Correo electrónico', value: detalle.e_mail || 'N/A' },
      { label: 'Dirección Proveedor', value: detalle.direccion_proveedor || 'N/A' },
      { label: 'Saldo', value: formatCurrency(detalle.saldo ?? 0) },
    ];
  }, [detalle]);

  return (
    <>
    <View className="flex-1" style={{ paddingHorizontal: 16, paddingTop: 14 }}>
      <View className="flex-row items-center" style={{ gap: 8 }}>
        <Pressable
          onPress={onBack}
          className="flex-row items-center rounded-xl border px-3 py-2"
          style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}
        >
          <MaterialIcons name="arrow-back" size={16} color={stitchColors.onSurface} />
          <Text className="ml-1 text-xs font-semibold" style={{ color: stitchColors.onSurface }}>
            Volver
          </Text>
        </Pressable>
      </View>

      <View className="flex-1" style={{ flexDirection: 'column', marginTop: 12 }}>
        <ScrollView
          className="flex-1"
          contentContainerStyle={{ paddingBottom: 24 }}
          showsVerticalScrollIndicator={false}
        >
          <View className="rounded-2xl border px-4 py-4" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}>
        <View className="flex-row items-start justify-between" style={{ gap: 8 }}>
          <View className="flex-1">
            <Text className="text-[11px] font-semibold uppercase" style={{ color: stitchColors.secondary, letterSpacing: 0.4 }}>
              Ficha del proveedor
            </Text>
            <Text className="mt-1 text-lg font-semibold" style={{ color: stitchColors.onBackground }}>
              {nombre}
            </Text>
            <Text className="mt-0.5 text-xs" style={{ color: stitchColors.onSurfaceVariant }}>
              Código: {codigo}
            </Text>
          </View>
          {detalle?.status ? (
            <View className="rounded-full px-2 py-1" style={{ backgroundColor: tone.bg }}>
              <Text className="text-[10px] font-bold" style={{ color: tone.text }}>
                {detalle.status}
              </Text>
            </View>
          ) : null}
        </View>

        {loading ? (
          <View className="mt-4 items-center">
            <ActivityIndicator size="large" color={stitchColors.primary} />
            <Text className="mt-2 text-xs" style={{ color: stitchColors.onSurfaceVariant }}>
              Cargando ficha del proveedor...
            </Text>
          </View>
        ) : error ? (
          <View className="mt-4 rounded-xl border px-3 py-3" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
            <Text className="text-xs" style={{ color: stitchColors.error }}>
              {error}
            </Text>
            <Pressable
              onPress={onRetry}
              className="mt-2 self-start rounded-lg px-3 py-2"
              style={{ backgroundColor: stitchColors.primary }}
            >
              <Text className="text-xs font-semibold" style={{ color: stitchColors.onPrimary }}>
                Reintentar
              </Text>
            </Pressable>
          </View>
        ) : detalle ? (
          <View className="mt-3 flex-row flex-wrap" style={{ gap: 6 }}>
            {dataRows.map((row) => (
              <View key={`ficha-${row.label}`} className="rounded-xl border px-3 py-2" style={{ width: fichaIsDesktop ? '32%' : '100%', borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
                <Text className="text-[10px] font-semibold uppercase" style={{ color: stitchColors.onSurfaceVariant, letterSpacing: 0.3 }}>
                  {row.label}
                </Text>
                <Text className="mt-0.5 text-sm" style={{ color: stitchColors.onSurface }}>
                  {row.value}
                </Text>
              </View>
            ))}
          </View>
        ) : null}

      {!loading && !error && detalle ? (
        <View className="mt-3 rounded-xl border px-3 py-3" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
          <Text className="text-xs font-semibold" style={{ color: stitchColors.secondary }}>
            Cuentas de pago
          </Text>
          {cuentas.length === 0 ? (
            <Text className="mt-2 text-sm" style={{ color: stitchColors.onSurfaceVariant }}>
              Sin cuentas de pago registradas.
            </Text>
          ) : (
            <View className="mt-2" style={{ gap: 8 }}>
              {cuentas.map((cuenta, index) => {
                const cuentaTone = statusTone(cuenta.status ?? null);
                return (
                  <View
                    key={`cuenta-${cuenta.id_cuenta ?? index}`}
                    className="rounded-xl border px-3 py-2.5"
                    style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}
                  >
                    <View className="flex-row items-center justify-between" style={{ gap: 8 }}>
                      <Text className="flex-1 text-xs font-semibold" style={{ color: stitchColors.onBackground }}>
                        {cuenta.nombre_banco || 'Banco no indicado'}
                      </Text>
                      {cuenta.es_principal ? (
                        <View className="rounded-full px-2 py-1" style={{ backgroundColor: `${stitchColors.secondary}18` }}>
                          <Text className="text-[10px] font-bold" style={{ color: stitchColors.secondary }}>
                            Principal
                          </Text>
                        </View>
                      ) : null}
                    </View>
                    <Text className="mt-1 text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                      Tipo: {cuenta.tipo_cuenta || 'N/A'} · Moneda: {cuenta.moneda || 'N/A'}
                    </Text>
                    <Text className="mt-0.5 text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                      Cuenta: {cuenta.numero_cuenta || 'N/A'}
                    </Text>
                    <Text className="mt-0.5 text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                      Titular: {cuenta.titular || 'N/A'} · Doc: {cuenta.documento_titular || 'N/A'}
                    </Text>
                    <View className="mt-1 flex-row items-center justify-between">
                      <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                        Sucursal: {sucursalDescripcion(cuenta.sucursal, sucursales)}
                      </Text>
                      {cuenta.status ? (
                        <View className="rounded-full px-2 py-1" style={{ backgroundColor: cuentaTone.bg }}>
                          <Text className="text-[10px] font-bold" style={{ color: cuentaTone.text }}>
                            {cuenta.status}
                          </Text>
                        </View>
                      ) : null}
                    </View>
                  </View>
                );
              })}
            </View>
          )}
        </View>
      ) : null}
      </View>
    </ScrollView>

    <View style={{ flex: 1, marginTop: 12, paddingBottom: 24 }}>
      <View className="rounded-2xl border px-4 py-4" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}>
        <Text className="text-[11px] font-semibold uppercase" style={{ color: stitchColors.secondary, letterSpacing: 0.4 }}>
          Sección
        </Text>
        <Pressable
          onPress={() => setShowSeccionSelector(true)}
          className="mt-2 rounded-lg border px-3 py-2.5"
          style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}
        >
          <View className="flex-row items-center justify-between">
            <Text className="text-sm font-semibold" style={{ color: stitchColors.onSurface }}>
              {seccionesOpciones.find((opcion) => opcion.value === seccionActiva)?.label ?? 'Seleccionar'}
            </Text>
            <MaterialIcons name="expand-more" size={18} color={stitchColors.onSurfaceVariant} />
          </View>
        </Pressable>

        {seccionActiva === 'estado' ? (
          <View className="mt-3" style={{ gap: 8 }}>
            {loadingEstadoCuenta ? (
              <View className="items-center" style={{ paddingVertical: 24 }}>
                <ActivityIndicator size="large" color={stitchColors.primary} />
                <Text className="mt-2 text-xs" style={{ color: stitchColors.onSurfaceVariant }}>
                  Cargando estado de cuenta...
                </Text>
              </View>
            ) : errorEstadoCuenta ? (
              <View className="rounded-xl border px-3 py-3" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
                <Text className="text-xs" style={{ color: stitchColors.error }}>
                  {errorEstadoCuenta}
                </Text>
              </View>
            ) : estadoCuenta.length === 0 ? (
              <View className="items-center" style={{ paddingVertical: 24 }}>
                <MaterialIcons name="folder-open" size={28} color={stitchColors.outline} />
                <Text className="mt-2 text-xs" style={{ color: stitchColors.onSurfaceVariant }}>
                  Sin movimientos para el estado de cuenta.
                </Text>
              </View>
            ) : (
              <>
                <View className="flex-row" style={{ gap: 8 }}>
                  <View className="flex-1 rounded-xl border px-3 py-2" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
                    <Text className="text-[10px] font-semibold uppercase" style={{ color: stitchColors.onSurfaceVariant, letterSpacing: 0.3 }}>
                      Débito
                    </Text>
                    <Text className="mt-0.5 text-sm font-semibold" style={{ color: stitchColors.onBackground }}>
                      {formatCurrency(totalesEstadoCuenta.saldoDebito)}
                    </Text>
                  </View>
                  <View className="flex-1 rounded-xl border px-3 py-2" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
                    <Text className="text-[10px] font-semibold uppercase" style={{ color: stitchColors.onSurfaceVariant, letterSpacing: 0.3 }}>
                      Crédito
                    </Text>
                    <Text className="mt-0.5 text-sm font-semibold" style={{ color: stitchColors.onBackground }}>
                      {formatCurrency(totalesEstadoCuenta.saldoCredito)}
                    </Text>
                  </View>
                  <View className="flex-1 rounded-xl border px-3 py-2" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}>
                    <Text className="text-[10px] font-semibold uppercase" style={{ color: stitchColors.secondary, letterSpacing: 0.3 }}>
                      Saldo neto
                    </Text>
                    <Text className="mt-0.5 text-sm font-semibold" style={{ color: stitchColors.primary }}>
                      {formatCurrency(totalesEstadoCuenta.saldoNeto)}
                    </Text>
                  </View>
                </View>

                <View style={{ gap: 8 }}>
                  {estadoCuenta.map((row, index) => {
                    const rowTone = statusTone(row.status);
                    const esCredito = (row.tipo ?? '').toUpperCase() === 'CREDITO';
                    return (
                      <View
                        key={`ec-${row.doc ?? index}-${index}`}
                        className="rounded-xl border px-3 py-2.5"
                        style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}
                      >
                        <View className="flex-row items-center justify-between" style={{ gap: 8 }}>
                          <Text className="flex-1 text-xs font-semibold" style={{ color: stitchColors.onBackground }}>
                            {row.tipo_documento || 'N/A'} · {row.numero_documento || 'N/A'}
                          </Text>
                          <View className="rounded-full px-2 py-1" style={{ backgroundColor: rowTone.bg }}>
                            <Text className="text-[10px] font-bold" style={{ color: rowTone.text }}>
                              {row.status || 'N/A'}
                            </Text>
                          </View>
                        </View>
                        {row.descripcion ? (
                          <Text className="mt-1 text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                            {row.descripcion}
                          </Text>
                        ) : null}
                        <View className="mt-1 flex-row flex-wrap" style={{ gap: 6 }}>
                          <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                            Emisión: {formatDate(row.fecha_emision)}
                          </Text>
                          <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                            · Vence: {formatDate(row.fecha_vencimiento)}
                          </Text>
                          {row.nro_fiscal ? (
                            <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                              · Fiscal: {row.nro_fiscal}
                            </Text>
                          ) : null}
                          {row.nro_control ? (
                            <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                              · Control: {row.nro_control}
                            </Text>
                          ) : null}
                        </View>
                        <View className="mt-1 flex-row items-center justify-between">
                          <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                            {esCredito ? 'Crédito' : 'Débito'}
                          </Text>
                          <Text className="text-sm font-semibold" style={{ color: esCredito ? '#0f8a4b' : '#b42318' }}>
                            {formatCurrency(row.saldo ?? 0)}
                          </Text>
                        </View>
                        <View className="mt-1 flex-row flex-wrap" style={{ gap: 6 }}>
                          {row.sub_total != null ? (
                            <Text className="text-[10px]" style={{ color: stitchColors.onSurfaceVariant }}>
                              Subtotal: {formatCurrency(row.sub_total)}
                            </Text>
                          ) : null}
                          {row.total_neto != null ? (
                            <Text className="text-[10px]" style={{ color: stitchColors.onSurfaceVariant }}>
                              · Neto: {formatCurrency(row.total_neto)}
                            </Text>
                          ) : null}
                          {row.tasa_cambio != null ? (
                            <Text className="text-[10px]" style={{ color: stitchColors.onSurfaceVariant }}>
                              · Tasa: {formatNumber(row.tasa_cambio)}
                            </Text>
                          ) : null}
                          {row.sucursal ? (
                            <Text className="text-[10px]" style={{ color: stitchColors.onSurfaceVariant }}>
                              · Suc: {sucursalDescripcion(row.sucursal, sucursales)}
                            </Text>
                          ) : null}
                        </View>
                      </View>
                    );
                  })}
                </View>
              </>
            )}
          </View>
        ) : seccionActiva === 'productos' ? (
          <View className="mt-3" style={{ gap: 8 }}>
            {loadingProductos ? (
              <View className="items-center" style={{ paddingVertical: 24 }}>
                <ActivityIndicator size="large" color={stitchColors.primary} />
                <Text className="mt-2 text-xs" style={{ color: stitchColors.onSurfaceVariant }}>
                  Cargando productos del proveedor...
                </Text>
              </View>
            ) : errorProductos ? (
              <View className="rounded-xl border px-3 py-3" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
                <Text className="text-xs" style={{ color: stitchColors.error }}>
                  {errorProductos}
                </Text>
              </View>
            ) : productosProveedor.length === 0 ? (
              <View className="items-center" style={{ paddingVertical: 24 }}>
                <MaterialIcons name="folder-open" size={28} color={stitchColors.outline} />
                <Text className="mt-2 text-xs" style={{ color: stitchColors.onSurfaceVariant }}>
                  Sin productos registrados para este proveedor.
                </Text>
              </View>
            ) : (
              <>
                <View className="flex-row" style={{ gap: 8 }}>
                  <View className="flex-1 rounded-xl border px-3 py-2" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
                    <Text className="text-[10px] font-semibold uppercase" style={{ color: stitchColors.onSurfaceVariant, letterSpacing: 0.3 }}>
                      Productos
                    </Text>
                    <Text className="mt-0.5 text-sm font-semibold" style={{ color: stitchColors.onBackground }}>
                      {formatNumber(productosProveedor.length)}
                    </Text>
                  </View>
                  <View className="flex-1 rounded-xl border px-3 py-2" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
                    <Text className="text-[10px] font-semibold uppercase" style={{ color: stitchColors.onSurfaceVariant, letterSpacing: 0.3 }}>
                      Compras
                    </Text>
                    <Text className="mt-0.5 text-sm font-semibold" style={{ color: stitchColors.onBackground }}>
                      {formatNumber(totalesProductos.comprasTotales)}
                    </Text>
                  </View>
                  <View className="flex-1 rounded-xl border px-3 py-2" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}>
                    <Text className="text-[10px] font-semibold uppercase" style={{ color: stitchColors.secondary, letterSpacing: 0.3 }}>
                      Monto total
                    </Text>
                    <Text className="mt-0.5 text-sm font-semibold" style={{ color: stitchColors.primary }}>
                      {formatCurrency(totalesProductos.montoTotal)}
                    </Text>
                  </View>
                </View>

                <View style={{ gap: 8 }}>
                  {productosProveedor.map((row, index) => (
                    <View
                      key={`prod-${(row.codigo_producto ?? '').trim() || index}-${index}`}
                      className="rounded-xl border px-3 py-2.5"
                      style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}
                    >
                      <View className="flex-row items-center justify-between" style={{ gap: 8 }}>
                        <Text className="flex-1 text-xs font-semibold" style={{ color: stitchColors.onBackground }}>
                          {row.codigo_producto || 'N/A'}
                        </Text>
                        <Text className="text-[10px]" style={{ color: stitchColors.onSurfaceVariant }}>
                          {formatNumber(row.compras_count ?? 0)} compras
                        </Text>
                      </View>
                      {row.nombre_producto ? (
                        <Text className="mt-1 text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                          {row.nombre_producto}
                        </Text>
                      ) : null}
                      <View className="mt-1 flex-row flex-wrap" style={{ gap: 6 }}>
                        <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                          Cantidad: {formatNumber(row.cantidad_total ?? 0)}
                        </Text>
                        <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                          · Última compra: {formatDate(row.ultima_fecha_compra)}
                        </Text>
                      </View>
                      <View className="mt-1 flex-row items-center justify-between">
                        <Text className="text-[10px] font-semibold uppercase" style={{ color: stitchColors.secondary, letterSpacing: 0.3 }}>
                          Monto total
                        </Text>
                        <Text className="text-sm font-semibold" style={{ color: stitchColors.primary }}>
                          {formatCurrency(row.monto_total ?? 0)}
                        </Text>
                      </View>
                    </View>
                  ))}
                </View>
              </>
            )}
          </View>
        ) : seccionActiva === 'compras' ? (
          <View className="mt-3" style={{ gap: 8 }}>
            {loadingCompras ? (
              <View className="items-center" style={{ paddingVertical: 24 }}>
                <ActivityIndicator size="large" color={stitchColors.primary} />
                <Text className="mt-2 text-xs" style={{ color: stitchColors.onSurfaceVariant }}>
                  Cargando compras del proveedor...
                </Text>
              </View>
            ) : errorCompras ? (
              <View className="rounded-xl border px-3 py-3" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
                <Text className="text-xs" style={{ color: stitchColors.error }}>
                  {errorCompras}
                </Text>
              </View>
            ) : comprasProveedor.length === 0 ? (
              <View className="items-center" style={{ paddingVertical: 24 }}>
                <MaterialIcons name="folder-open" size={28} color={stitchColors.outline} />
                <Text className="mt-2 text-xs" style={{ color: stitchColors.onSurfaceVariant }}>
                  Sin compras registradas para este proveedor.
                </Text>
              </View>
            ) : (
              <>
                <View className="flex-row" style={{ gap: 8 }}>
                  <View className="flex-1 rounded-xl border px-3 py-2" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
                    <Text className="text-[10px] font-semibold uppercase" style={{ color: stitchColors.onSurfaceVariant, letterSpacing: 0.3 }}>
                      Compras
                    </Text>
                    <Text className="mt-0.5 text-sm font-semibold" style={{ color: stitchColors.onBackground }}>
                      {formatNumber(comprasProveedor.length)}
                    </Text>
                  </View>
                  <View className="flex-1 rounded-xl border px-3 py-2" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
                    <Text className="text-[10px] font-semibold uppercase" style={{ color: stitchColors.onSurfaceVariant, letterSpacing: 0.3 }}>
                      Total Bs
                    </Text>
                    <Text className="mt-0.5 text-sm font-semibold" style={{ color: stitchColors.onBackground }}>
                      {formatCurrency(totalesCompras.totalBs)}
                    </Text>
                  </View>
                  <View className="flex-1 rounded-xl border px-3 py-2" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}>
                    <Text className="text-[10px] font-semibold uppercase" style={{ color: stitchColors.secondary, letterSpacing: 0.3 }}>
                      Total USD
                    </Text>
                    <Text className="mt-0.5 text-sm font-semibold" style={{ color: stitchColors.primary }}>
                      {formatCurrency(totalesCompras.totalUsd)}
                    </Text>
                  </View>
                </View>

                <View style={{ gap: 8 }}>
                  {comprasProveedor.map((row, index) => {
                    const rowTone = statusTone(row.estado);
                    return (
                      <View
                        key={`compra-${row.id_compra ?? index}-${index}`}
                        className="rounded-xl border px-3 py-2.5"
                        style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}
                      >
                        <View className="flex-row items-center justify-between" style={{ gap: 8 }}>
                          <Text className="flex-1 text-xs font-semibold" style={{ color: stitchColors.onBackground }}>
                            Factura: {row.numero_factura || 'N/A'}
                          </Text>
                          <View className="rounded-full px-2 py-1" style={{ backgroundColor: rowTone.bg }}>
                            <Text className="text-[10px] font-bold" style={{ color: rowTone.text }}>
                              {row.estado || 'N/A'}
                            </Text>
                          </View>
                        </View>
                        <View className="mt-1 flex-row flex-wrap" style={{ gap: 6 }}>
                          <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                            Control: {row.nro_control || 'N/A'}
                          </Text>
                          <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>
                            · Fecha: {formatDate(row.fecha_factura)}
                          </Text>
                        </View>
                        <View className="mt-1 flex-row items-center justify-between">
                          <Text className="text-[10px] font-semibold uppercase" style={{ color: stitchColors.onSurfaceVariant, letterSpacing: 0.3 }}>
                            Total Bolívares
                          </Text>
                          <Text className="text-sm font-semibold" style={{ color: stitchColors.onBackground }}>
                            {formatCurrency(row.total_bs ?? 0)}
                          </Text>
                        </View>
                        <View className="mt-1 flex-row items-center justify-between">
                          <Text className="text-[10px] font-semibold uppercase" style={{ color: stitchColors.secondary, letterSpacing: 0.3 }}>
                            Total Dólares
                          </Text>
                          <Text className="text-sm font-semibold" style={{ color: stitchColors.primary }}>
                            {formatCurrency(row.total_usd ?? 0)}
                          </Text>
                        </View>
                      </View>
                    );
                  })}
                </View>
              </>
            )}
          </View>
        ) : (
          <View className="mt-3 items-center" style={{ paddingVertical: 24 }}>
            <MaterialIcons name="folder-open" size={28} color={stitchColors.outline} />
            <Text className="mt-2 text-xs" style={{ color: stitchColors.onSurfaceVariant }}>
              Contenido de «{seccionesOpciones.find((opcion) => opcion.value === seccionActiva)?.label ?? ''}» próximamente.
            </Text>
          </View>
        )}
      </View>
    </View>
    </View>
    </View>

    <Modal
      visible={showSeccionSelector}
      transparent
      animationType="fade"
      onRequestClose={() => setShowSeccionSelector(false)}
    >
      <View className="flex-1 justify-center px-6" style={{ backgroundColor: 'rgba(0,0,0,0.32)' }}>
        <View className="rounded-2xl border px-4 py-4" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}>
          <View className="mb-3 flex-row items-center justify-between">
            <Text className="text-sm font-semibold" style={{ color: stitchColors.onBackground }}>
              Seleccionar sección
            </Text>
            <Pressable onPress={() => setShowSeccionSelector(false)}>
              <MaterialIcons name="close" size={20} color={stitchColors.onSurfaceVariant} />
            </Pressable>
          </View>

          <ScrollView style={{ maxHeight: 320 }}>
            {seccionesOpciones.map((opcion) => {
              const active = seccionActiva === opcion.value;
              return (
                <Pressable
                  key={`seccion-option-${opcion.value}`}
                  className="mb-1 flex-row items-center justify-between rounded-lg px-3 py-2.5"
                  style={{ backgroundColor: active ? `${stitchColors.primary}18` : stitchColors.surfaceLowest }}
                  onPress={() => {
                    setSeccionActiva(opcion.value);
                    setShowSeccionSelector(false);
                  }}
                >
                  <Text className="text-sm font-semibold" style={{ color: active ? stitchColors.primary : stitchColors.onSurface }}>
                    {opcion.label}
                  </Text>
                  {active ? <MaterialIcons name="check" size={16} color={stitchColors.primary} /> : null}
                </Pressable>
              );
            })}
          </ScrollView>
        </View>
      </View>
    </Modal>
    </>
  );
}

export function DashboardProveedoresCxpScreen({ onSessionExpired, onFallbackToWeb }: DashboardProveedoresCxpScreenProps) {
  const { width } = useWindowDimensions();
  const isCompact = width < 768;
  const isDesktop = width >= 1200;
  const contentMaxWidth = isDesktop ? 1480 : undefined;
  const metricCardWidth: DimensionValue = isDesktop ? '32.6%' : isCompact ? '100%' : '48.9%';
  const filterCardWidth: DimensionValue = isDesktop ? '32.6%' : '100%';
  const desktopColumnHeight = 520;
  const statusQuickOptions = ['EN INVENTARIO', 'EN ESPERA', 'ANULADO'] as const;
  const { loading, error, setError, runProtected } = useProtectedResource({ onSessionExpired });
  const [sucursal, setSucursal] = useState('');
  const [sucursalesPermitidas, setSucursalesPermitidas] = useState<SucursalAsignada[]>([]);
  const [sucursalesReady, setSucursalesReady] = useState(false);
  const [showSucursalSelector, setShowSucursalSelector] = useState(false);
  const [busquedaSucursal, setBusquedaSucursal] = useState('');
  const [status, setStatus] = useState('EN INVENTARIO');
  const [showStatusSelector, setShowStatusSelector] = useState(false);
  const [showFechaSelector, setShowFechaSelector] = useState(false);
  const [showFiltersPanel, setShowFiltersPanel] = useState(false);
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');
  const [draftFrom, setDraftFrom] = useState('');
  const [draftTo, setDraftTo] = useState('');
  const [draftFromText, setDraftFromText] = useState('');
  const [draftToText, setDraftToText] = useState('');
  const [rangeStep, setRangeStep] = useState<'start' | 'end'>('start');
  const [calendarCursor, setCalendarCursor] = useState(() => startOfMonthDate(new Date()));
  const [deudaTotal, setDeudaTotal] = useState({
    total_deuda_pendiente: 0,
    total_documentos_pendientes: 0,
    proveedores_con_deuda: 0,
    top_proveedores_deuda: [] as ProveedorDeudaTop[],
  });
  const [deudaMensual, setDeudaMensual] = useState<{ labels: string[]; total_deuda: number[] }>({ labels: [], total_deuda: [] });
  const [pendientes, setPendientes] = useState<ProveedoresFacturaPendiente[]>([]);
  const [codProveedorBusqueda, setCodProveedorBusqueda] = useState('');
  const [showProveedorSelector, setShowProveedorSelector] = useState(false);
  const [busquedaProveedor, setBusquedaProveedor] = useState('');
  const [paginaTopDeuda, setPaginaTopDeuda] = useState(1);
  const [paginaPendientes, setPaginaPendientes] = useState(1);
  const fallbackTriggeredRef = useRef(false);

  const [fichaProveedorCodigo, setFichaProveedorCodigo] = useState<string | null>(null);
  const [fichaDetalle, setFichaDetalle] = useState<ProveedorFicha | null>(null);
  const [fichaCuentas, setFichaCuentas] = useState<ProveedorCuentaPago[]>([]);
  const [loadingFicha, setLoadingFicha] = useState(false);
  const [errorFicha, setErrorFicha] = useState('');

  const loadDashboard = useCallback(async () => {
    const sucursalNormalizada = sucursal.trim();
    const sucursalValida = sucursalesPermitidas.some(
      (item) => (item.codigo_sucursal ?? '').trim() === sucursalNormalizada,
    );
    const sucursalConsulta = sucursalValida ? sucursalNormalizada : '';

    const rango = normalizeDateRange(dateFrom, dateTo);
    const fechaCorte = rango.to || rango.from || null;

    const result = await runProtected(async (accessToken) => {
      return fetchDashboardProveedoresCxp(accessToken, {
        sucursal: sucursalConsulta || null,
        status: status.trim() ? status.trim() : null,
        fechaCorte,
        fechaDesde: rango.from || null,
        fechaHasta: rango.to || rango.from || null,
        top: 10,
        meses: 12,
        limit: fechaCorte ? 500 : (sucursalConsulta ? 25 : 500),
        offset: 0,
      });
    });

    if (!result) {
      return;
    }

    setDeudaTotal(result.deudaTotal ?? {
      total_deuda_pendiente: 0,
      total_documentos_pendientes: 0,
      proveedores_con_deuda: 0,
      top_proveedores_deuda: [],
    });
    setDeudaMensual(result.deudaMensual ?? { labels: [], total_deuda: [] });
    setPendientes(result.pendientes);
  }, [dateFrom, dateTo, runProtected, status, sucursal, sucursalesPermitidas]);

  useEffect(() => {
    let cancelled = false;

    async function loadSucursalesPermitidas() {
      const result = await runProtected(async (accessToken) => {
        let sucursalActual = '';
        let empresaActual = '';

        try {
          const claims = await fetchValidateTokenClaims(accessToken);
          sucursalActual = (getBranchCodeFromClaims(claims) ?? '').trim();
          empresaActual = (getCompanyCodeFromClaims(claims) ?? '').trim();
        } catch {
          // Si validateToken falla en este ambiente, seguimos con fallback local.
        }

        if (!empresaActual) {
          try {
            const seguridad = await getSeguridadUser();
            empresaActual = (seguridad?.codigo_empresa ?? '').trim();
            if (!sucursalActual) {
              sucursalActual = (seguridad?.codigo_sucursal ?? '').trim();
            }
          } catch {
            // Si no hay sesion local, mantenemos fallback vacio.
          }
        }

        let sucursales: SucursalAsignada[] = [];
        if (empresaActual) {
          try {
            sucursales = await fetchSucursalesAsignadasUsuarioPorEmpresa(accessToken, empresaActual);
          } catch {
            sucursales = [];
          }
        }

        sucursales = [...sucursales].sort((a, b) => {
          const left = (a.descripcion || a.codigo_sucursal || '').trim();
          const right = (b.descripcion || b.codigo_sucursal || '').trim();
          return left.localeCompare(right, 'es', { sensitivity: 'base' });
        });

        return {
          sucursales,
          sucursalActual,
        };
      });

      if (!result || cancelled) {
        if (!cancelled) {
          setSucursalesReady(true);
        }
        return;
      }

      setSucursalesPermitidas(result.sucursales);

      const sucursalActualNormalizada = result.sucursalActual.trim();
      const sucursalExiste = result.sucursales.some(
        (item) => item.codigo_sucursal?.trim() === sucursalActualNormalizada,
      );

      if (sucursalExiste) {
        setSucursal(sucursalActualNormalizada);
      } else {
        setSucursal('');
      }

      setSucursalesReady(true);
    }

    void loadSucursalesPermitidas();

    return () => {
      cancelled = true;
    };
  }, [runProtected]);

  useEffect(() => {
    if (!sucursalesReady) {
      return;
    }

    void loadDashboard();
  }, [loadDashboard, sucursalesReady]);

  useEffect(() => {
    if (!error || !onFallbackToWeb || fallbackTriggeredRef.current) {
      return;
    }

    const normalized = error.toLowerCase();
    const shouldFallback = normalized.includes('proveedoresdashboarddeudatotal')
      || normalized.includes('proveedoresdashboarddeudamensual')
      || normalized.includes('proveedoresfacturaspendientes')
      || normalized.includes('cannot query field')
      || normalized.includes('http 400')
      || normalized.includes('bad request')
      || normalized.includes('no se pudo consultar dashboard de proveedores cxp');

    if (!shouldFallback) {
      return;
    }

    fallbackTriggeredRef.current = true;
    onFallbackToWeb();
  }, [error, onFallbackToWeb]);

  const trendRows = useMemo(() => {
    return deudaMensual.labels.map((label, index) => ({
      label,
      total: deudaMensual.total_deuda[index] ?? 0,
    }));
  }, [deudaMensual.labels, deudaMensual.total_deuda]);

  const maxTrend = useMemo(() => {
    return trendRows.reduce((acc, row) => Math.max(acc, row.total), 0);
  }, [trendRows]);

  const proveedoresOpciones = useMemo(() => {
    const map = new Map<string, { codigo: string; nombre: string }>();
    const add = (codigo?: string | null, nombre?: string | null) => {
      const code = (codigo || '').trim();
      if (!code) return;
      const current = map.get(code);
      const name = (nombre || '').trim();
      if (!current) {
        map.set(code, { codigo: code, nombre: name });
        return;
      }
      if (name && !current.nombre) current.nombre = name;
    };
    deudaTotal.top_proveedores_deuda.forEach((row) => add(row.cod_proveedor, row.nombre_proveedor));
    pendientes.forEach((row) => add(row.cod_proveedor, row.nombre_proveedor));
    return Array.from(map.values()).sort((a, b) =>
      (a.nombre || a.codigo).localeCompare(b.nombre || b.codigo, 'es', { sensitivity: 'base' }),
    );
  }, [deudaTotal.top_proveedores_deuda, pendientes]);

  const selectedProveedorLabel = useMemo(() => {
    if (!codProveedorBusqueda.trim()) return 'Todos los proveedores';
    const selected = proveedoresOpciones.find(
      (item) => item.codigo.toUpperCase() === codProveedorBusqueda.trim().toUpperCase(),
    );
    if (!selected) return codProveedorBusqueda;
    return `${selected.nombre || 'Sin nombre'} - ${selected.codigo}`;
  }, [codProveedorBusqueda, proveedoresOpciones]);

  const proveedoresFiltrados = useMemo(() => {
    const q = busquedaProveedor.trim().toLowerCase();
    if (!q) return proveedoresOpciones;
    return proveedoresOpciones.filter((item) =>
      item.nombre.toLowerCase().includes(q) || item.codigo.toLowerCase().includes(q),
    );
  }, [busquedaProveedor, proveedoresOpciones]);

  const selectedSucursalLabel = useMemo(() => {
    if (!sucursal) {
      return 'Todas las sucursales';
    }

    const selected = sucursalesPermitidas.find((item) => item.codigo_sucursal?.trim() === sucursal);
    if (!selected) {
      return sucursal;
    }

    return `${selected.descripcion?.trim() || 'Sin descripcion'} - ${selected.codigo_sucursal?.trim() ?? ''}`;
  }, [sucursal, sucursalesPermitidas]);

  const sucursalesFiltradas = useMemo(() => {
    const q = busquedaSucursal.trim().toLowerCase();
    if (!q) return sucursalesPermitidas;
    return sucursalesPermitidas.filter((item) => {
      const code = (item.codigo_sucursal ?? '').trim().toLowerCase();
      const description = (item.descripcion ?? '').trim().toLowerCase();
      return description.includes(q) || code.includes(q);
    });
  }, [busquedaSucursal, sucursalesPermitidas]);

  const todayIso = useMemo(() => formatISODate(new Date()), []);

  const calendarDays = useMemo(() => {
    const year = calendarCursor.getFullYear();
    const month = calendarCursor.getMonth();
    const totalDays = new Date(year, month + 1, 0).getDate();
    const startOffset = (new Date(year, month, 1).getDay() + 6) % 7;
    const days = Array.from({ length: totalDays }, (_, idx) => {
      const day = idx + 1;
      return { day, iso: `${year}-${pad2(month + 1)}-${pad2(day)}` };
    });
    const leading = Array.from({ length: startOffset }, () => null);
    const raw = [...leading, ...days];
    const trailing = Array.from({ length: Math.max(0, 42 - raw.length) }, () => null);
    return {
      label: `${MONTHS_ES[month] ?? ''} ${year}`,
      cells: [...raw, ...trailing],
    };
  }, [calendarCursor]);

  const selectedDateRangeLabel = useMemo(() => {
    if (!dateFrom && !dateTo) {
      return 'Seleccionar fecha';
    }
    if (dateFrom && dateTo) {
      if (dateFrom === dateTo) return formatDateReadable(dateFrom);
      return `${formatDateShort(dateFrom)} - ${formatDateShort(dateTo)}`;
    }
    if (dateFrom) return `Desde ${formatDateReadable(dateFrom)}`;
    return `Hasta ${formatDateReadable(dateTo)}`;
  }, [dateFrom, dateTo]);

  const hasDateRange = Boolean(dateFrom || dateTo);

  const selectedStatusLabel = useMemo(() => {
    const normalized = status.trim().toUpperCase();
    if (!normalized) {
      return 'Todos los status';
    }
    return normalized;
  }, [status]);

  const filtrosActivos = useMemo(() => {
    let count = 0;
    if (sucursal.trim()) count += 1;
    if (status.trim().toUpperCase() !== 'EN INVENTARIO') count += 1;
    if (hasDateRange) count += 1;
    if (codProveedorBusqueda.trim()) count += 1;
    return count;
  }, [codProveedorBusqueda, hasDateRange, status, sucursal]);

  const topDeudaOrdenado = useMemo(() => {
    return [...deudaTotal.top_proveedores_deuda].sort((a, b) => (b.total_deuda || 0) - (a.total_deuda || 0));
  }, [deudaTotal.top_proveedores_deuda]);
  const topDeudaPageSize = isCompact ? 4 : 6;
  const totalPaginasTopDeuda = useMemo(
    () => Math.max(1, Math.ceil(topDeudaOrdenado.length / topDeudaPageSize)),
    [topDeudaOrdenado.length, topDeudaPageSize],
  );
  const topDeudaPaginado = useMemo(() => {
    const start = (paginaTopDeuda - 1) * topDeudaPageSize;
    return topDeudaOrdenado.slice(start, start + topDeudaPageSize);
  }, [paginaTopDeuda, topDeudaOrdenado, topDeudaPageSize]);

  const pendientesOrdenados = useMemo(() => {
    const proveedor = codProveedorBusqueda.trim().toUpperCase();
    const filtrados = proveedor
      ? pendientes.filter((item) => (item.cod_proveedor || '').trim().toUpperCase() === proveedor)
      : pendientes;
    return [...filtrados].sort((a, b) => {
      const diasDiff = (b.dias_vencidos || 0) - (a.dias_vencidos || 0);
      if (diasDiff !== 0) return diasDiff;
      return (b.saldo || 0) - (a.saldo || 0);
    });
  }, [codProveedorBusqueda, pendientes]);

  const kpisVisibles = useMemo(() => {
    const proveedor = codProveedorBusqueda.trim().toUpperCase();
    if (!proveedor) {
      return {
        deuda: deudaTotal.total_deuda_pendiente,
        documentos: deudaTotal.total_documentos_pendientes,
        proveedoresLabel: 'Proveedores',
        proveedoresValue: formatNumber(deudaTotal.proveedores_con_deuda),
        proveedorSeleccionado: false,
      };
    }

    const fromTop = deudaTotal.top_proveedores_deuda.find(
      (item) => (item.cod_proveedor || '').trim().toUpperCase() === proveedor,
    );
    const deuda = fromTop
      ? (fromTop.total_deuda || 0)
      : pendientesOrdenados.reduce((acc, item) => acc + (item.saldo || 0), 0);
    const documentos = fromTop
      ? (fromTop.documentos_pendientes || 0)
      : pendientesOrdenados.length;
    const selected = proveedoresOpciones.find((item) => item.codigo.toUpperCase() === proveedor);
    const nombre = (selected?.nombre || fromTop?.nombre_proveedor || '').trim() || proveedor;

    return {
      deuda,
      documentos,
      proveedoresLabel: 'Proveedor',
      proveedoresValue: nombre,
      proveedorSeleccionado: true,
    };
  }, [
    codProveedorBusqueda,
    deudaTotal.proveedores_con_deuda,
    deudaTotal.top_proveedores_deuda,
    deudaTotal.total_deuda_pendiente,
    deudaTotal.total_documentos_pendientes,
    pendientesOrdenados,
    proveedoresOpciones,
  ]);
  const pendientesPageSize = isDesktop ? 5 : isCompact ? 5 : 8;
  const totalPaginasPendientes = useMemo(
    () => Math.max(1, Math.ceil(pendientesOrdenados.length / pendientesPageSize)),
    [pendientesOrdenados.length, pendientesPageSize],
  );
  const pendientesPaginados = useMemo(() => {
    const start = (paginaPendientes - 1) * pendientesPageSize;
    return pendientesOrdenados.slice(start, start + pendientesPageSize);
  }, [paginaPendientes, pendientesOrdenados, pendientesPageSize]);

  const deudaPorSucursal = useMemo(() => {
    const map = new Map<string, number>();
    pendientes.forEach((item) => {
      const code = (item.sucursal ?? '').trim() || 'Sin sucursal';
      map.set(code, (map.get(code) ?? 0) + (item.saldo || 0));
    });
    return Array.from(map.entries())
      .map(([code, total]) => ({
        codigo: code,
        descripcion: sucursalDescripcion(code, sucursalesPermitidas),
        total,
      }))
      .sort((a, b) => (b.total || 0) - (a.total || 0));
  }, [pendientes, sucursalesPermitidas]);

  const mostrarGraficoSucursales = !sucursal.trim() && !codProveedorBusqueda.trim() && deudaPorSucursal.length > 1;


  useEffect(() => {
    if (paginaTopDeuda > totalPaginasTopDeuda) {
      setPaginaTopDeuda(totalPaginasTopDeuda);
    }
  }, [paginaTopDeuda, totalPaginasTopDeuda]);

  useEffect(() => {
    if (paginaPendientes > totalPaginasPendientes) {
      setPaginaPendientes(totalPaginasPendientes);
    }
  }, [paginaPendientes, totalPaginasPendientes]);

  useEffect(() => {
    setPaginaTopDeuda(1);
    setPaginaPendientes(1);
  }, [sucursal, status, dateFrom, dateTo, codProveedorBusqueda, pendientes.length, deudaTotal.top_proveedores_deuda.length]);

  const handleOpenFicha = useCallback(async (codProveedor: string) => {
    const codigo = codProveedor.trim();
    if (!codigo) return;

    setFichaProveedorCodigo(codigo);
    setFichaDetalle(null);
    setFichaCuentas([]);
    setErrorFicha('');
    setLoadingFicha(true);

    const result = await runProtected(async (accessToken) => {
      const detalle = await fetchProveedorByCodigo(accessToken, codigo).catch((err: unknown) => {
        throw err;
      });
      let cuentas: ProveedorCuentaPago[] = [];
      if (detalle?.id_proveedor != null) {
        cuentas = await fetchProveedorCuentasPago(accessToken, detalle.id_proveedor).catch(() => []);
      }
      return { detalle, cuentas };
    });

    setLoadingFicha(false);

    if (!result) {
      return;
    }

    if (!result.detalle) {
      setErrorFicha(`No se encontró la ficha del proveedor ${codigo}.`);
      return;
    }

    setFichaDetalle(result.detalle);
    setFichaCuentas(result.cuentas);
  }, [runProtected]);

  const handleCloseFicha = useCallback(() => {
    setFichaProveedorCodigo(null);
    setFichaDetalle(null);
    setFichaCuentas([]);
    setErrorFicha('');
    setLoadingFicha(false);
  }, []);

  const openFechaSelector = () => {
    setDraftFrom(dateFrom);
    setDraftTo(dateTo);
    setDraftFromText(dateFrom ? formatDateReadable(dateFrom) : '');
    setDraftToText(dateTo ? formatDateReadable(dateTo) : '');
    setRangeStep(dateFrom && !dateTo ? 'end' : 'start');
    setCalendarCursor(startOfMonthDate(parseISODate(dateTo || dateFrom) ?? new Date()));
    setShowFechaSelector(true);
  };

  const applyDraftRange = (from: string, to: string) => {
    const next = normalizeDateRange(from, to);
    setDateFrom(next.from);
    setDateTo(next.to);
    setShowFechaSelector(false);
  };

  const selectCalendarDay = (iso: string) => {
    if (rangeStep === 'start' || (draftFrom && draftTo)) {
      setDraftFrom(iso);
      setDraftTo('');
      setDraftFromText(formatDateReadable(iso));
      setDraftToText('');
      setRangeStep('end');
      setCalendarCursor(startOfMonthDate(parseISODate(iso) ?? new Date()));
      return;
    }
    if (iso < draftFrom) {
      setDraftTo(draftFrom);
      setDraftFrom(iso);
      setDraftToText(formatDateReadable(draftFrom));
      setDraftFromText(formatDateReadable(iso));
    } else {
      setDraftTo(iso);
      setDraftToText(formatDateReadable(iso));
    }
    setRangeStep('start');
  };

  const handleTypedDate = (target: 'from' | 'to', raw: string) => {
    const masked = formatDateInputMask(raw);
    const parsed = parseTypedDate(masked);

    if (target === 'from') {
      setDraftFromText(masked);
      setRangeStep('start');
      if (parsed) {
        setDraftFrom(parsed);
        setCalendarCursor(startOfMonthDate(parseISODate(parsed) ?? new Date()));
      } else if (!masked) {
        setDraftFrom('');
      }
      return;
    }

    setDraftToText(masked);
    setRangeStep('end');
    if (parsed) {
      setDraftTo(parsed);
      setCalendarCursor(startOfMonthDate(parseISODate(parsed) ?? new Date()));
    } else if (!masked) {
      setDraftTo('');
    }
  };

  const applyPreset = (preset: 'hoy' | '7d' | 'mes' | '30d') => {
    const end = new Date();
    if (preset === 'hoy') {
      const iso = formatISODate(end);
      applyDraftRange(iso, iso);
      return;
    }
    if (preset === 'mes') {
      applyDraftRange(formatISODate(startOfMonthDate(end)), formatISODate(end));
      return;
    }
    const daysBack = preset === '7d' ? 6 : 29;
    applyDraftRange(formatISODate(addDays(end, -daysBack)), formatISODate(end));
  };

  return (
    <View className="flex-1" style={{ backgroundColor: stitchColors.surfaceLowest }}>
      {fichaProveedorCodigo ? (
        <ProveedorFichaView
          codigo={fichaProveedorCodigo}
          detalle={fichaDetalle}
          cuentas={fichaCuentas}
          loading={loadingFicha}
          error={errorFicha}
          sucursales={sucursalesPermitidas}
          onBack={handleCloseFicha}
          onRetry={() => void handleOpenFicha(fichaProveedorCodigo)}
        />
      ) : (
      <>
      {loading ? (
        <View className="flex-1 items-center justify-center">
          <ActivityIndicator size="large" color={stitchColors.primary} />
          <Text className="mt-3 text-sm" style={{ color: stitchColors.onSurfaceVariant }}>
            Cargando dashboard de proveedores CxP...
          </Text>
        </View>
      ) : (
        <ScrollView
          className="flex-1"
          contentContainerStyle={{
            paddingHorizontal: 16,
            paddingBottom: 16,
            paddingTop: 10,
            alignItems: isDesktop ? 'center' : undefined,
          }}
          showsVerticalScrollIndicator={false}
        >
          <View style={isDesktop ? { width: '100%', maxWidth: contentMaxWidth } : undefined}>
          <View className="mt-3 rounded-xl border px-3 py-2" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface, paddingVertical: 6 }}>
            <Pressable onPress={() => setShowFiltersPanel((current) => !current)}>
              <View className="flex-row items-center justify-between" style={{ gap: 8 }}>
                <View className="flex-1">
                  <Text className="text-[11px] font-semibold" style={{ color: stitchColors.onSurfaceVariant }}>
                    Filtros {filtrosActivos > 0 ? `(${filtrosActivos} activos)` : '(sin cambios)'}
                  </Text>
                </View>
                <MaterialIcons
                  name={showFiltersPanel ? 'expand-less' : 'expand-more'}
                  size={18}
                  color={stitchColors.onSurfaceVariant}
                />
              </View>
            </Pressable>

            {showFiltersPanel ? (
              <View className="mt-2 flex-row flex-wrap" style={{ gap: 8 }}>
                <View className="rounded-xl border px-3 py-2" style={{ flex: isDesktop ? 1 : undefined, width: isDesktop ? undefined : filterCardWidth, borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
                  <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>Sucursal (opcional)</Text>
                  <Pressable
                    className="mt-1 rounded-lg border px-2 py-2"
                    style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}
                    onPress={() => setShowSucursalSelector(true)}
                  >
                    <View className="flex-row items-center justify-between">
                      <Text className="flex-1 text-xs" numberOfLines={1} style={{ color: stitchColors.onSurface }}>
                        {selectedSucursalLabel}
                      </Text>
                      <MaterialIcons name="expand-more" size={16} color={stitchColors.onSurfaceVariant} />
                    </View>
                  </Pressable>
                </View>

                <View className="rounded-xl border px-3 py-2" style={{ flex: isDesktop ? 1 : undefined, width: isDesktop ? undefined : filterCardWidth, borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
                  <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>Status de compra (opcional)</Text>
                  <Pressable
                    className="mt-1 rounded-lg border px-2 py-2"
                    style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}
                    onPress={() => setShowStatusSelector(true)}
                  >
                    <View className="flex-row items-center justify-between">
                      <Text className="flex-1 text-xs" numberOfLines={1} style={{ color: stitchColors.onSurface }}>
                        {selectedStatusLabel}
                      </Text>
                      <MaterialIcons name="expand-more" size={16} color={stitchColors.onSurfaceVariant} />
                    </View>
                  </Pressable>
                </View>

                <View className="rounded-xl border px-3 py-2" style={{ flex: isDesktop ? 1 : undefined, width: isDesktop ? undefined : filterCardWidth, borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
                  <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>Rango de fechas (opcional)</Text>
                  <Pressable
                    className="mt-1 rounded-lg border px-2 py-2"
                    style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}
                    onPress={openFechaSelector}
                  >
                    <View className="flex-row items-center justify-between">
                      <Text className="flex-1 text-xs" numberOfLines={1} style={{ color: stitchColors.onSurface }}>
                        {selectedDateRangeLabel}
                      </Text>
                      <MaterialIcons name="date-range" size={16} color={stitchColors.onSurfaceVariant} />
                    </View>
                  </Pressable>
                </View>

                <View className="rounded-xl border px-3 py-2" style={{ flex: isDesktop ? 1 : undefined, width: isDesktop ? undefined : filterCardWidth, borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surfaceLowest }}>
                  <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>Proveedor (opcional)</Text>
                  <Pressable
                    className="mt-1 rounded-lg border px-2 py-2"
                    style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}
                    onPress={() => setShowProveedorSelector(true)}
                  >
                    <View className="flex-row items-center justify-between">
                      <Text className="flex-1 text-xs" numberOfLines={1} style={{ color: stitchColors.onSurface }}>
                        {selectedProveedorLabel}
                      </Text>
                      <MaterialIcons name="expand-more" size={16} color={stitchColors.onSurfaceVariant} />
                    </View>
                  </Pressable>
                </View>
              </View>
            ) : null}
          </View>

          {error ? (
            <Text className="mt-2 text-xs" style={{ color: stitchColors.error }}>
              {error}
            </Text>
          ) : null}

          <View className="mb-3 flex-row flex-wrap" style={{ gap: 8 }}>
            <View
              className="rounded-2xl border px-3 py-3"
              style={{ width: metricCardWidth, borderColor: stitchColors.outlineVariant, backgroundColor: kpiCardTheme('deuda').bg }}
            >
              <View className="flex-row items-center justify-between">
                <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>Deuda pendiente</Text>
                <MaterialIcons name="account-balance-wallet" size={16} color={kpiCardTheme('deuda').icon} />
              </View>
              <Text className="mt-1 text-lg font-semibold" style={{ color: kpiCardTheme('deuda').value }}>
                {formatCurrency(kpisVisibles.deuda)}
              </Text>
            </View>
            <View
              className="rounded-2xl border px-3 py-3"
              style={{ width: metricCardWidth, borderColor: stitchColors.outlineVariant, backgroundColor: kpiCardTheme('documentos').bg }}
            >
              <View className="flex-row items-center justify-between">
                <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>Documentos</Text>
                <MaterialIcons name="description" size={16} color={kpiCardTheme('documentos').icon} />
              </View>
              <Text className="mt-1 text-xl font-semibold" style={{ color: stitchColors.onBackground }}>{formatNumber(kpisVisibles.documentos)}</Text>
            </View>
            <View
              className="rounded-2xl border px-3 py-3"
              style={{ width: metricCardWidth, borderColor: stitchColors.outlineVariant, backgroundColor: kpiCardTheme('proveedores').bg }}
            >
              <View className="flex-row items-center justify-between">
                <Text className="text-[11px]" style={{ color: stitchColors.onSurfaceVariant }}>{kpisVisibles.proveedoresLabel}</Text>
                <MaterialIcons name="groups" size={16} color={kpiCardTheme('proveedores').icon} />
              </View>
              <Text
                className={kpisVisibles.proveedorSeleccionado ? 'mt-1 text-base font-semibold' : 'mt-1 text-xl font-semibold'}
                numberOfLines={2}
                style={{ color: kpiCardTheme('proveedores').value }}
              >
                {kpisVisibles.proveedoresValue}
              </Text>
            </View>
          </View>

          {isDesktop ? (
            <View className="mb-3 flex-row items-stretch" style={{ gap: 8, height: desktopColumnHeight }}>
              <View style={{ flex: 1, height: desktopColumnHeight }}>
                <PendientesTable
                  items={pendientesPaginados}
                  page={paginaPendientes}
                  totalPages={totalPaginasPendientes}
                  totalItems={pendientesOrdenados.length}
                  onPrevPage={() => setPaginaPendientes((prev) => Math.max(1, prev - 1))}
                  onNextPage={() => setPaginaPendientes((prev) => Math.min(totalPaginasPendientes, prev + 1))}
                  panelHeight={desktopColumnHeight}
                  sucursales={sucursalesPermitidas}
                  onOpenProveedor={handleOpenFicha}
                />
              </View>
              <View style={{ flex: 1, height: desktopColumnHeight }}>
                <TopDeudaList
                  items={topDeudaPaginado}
                  page={paginaTopDeuda}
                  totalPages={totalPaginasTopDeuda}
                  totalItems={topDeudaOrdenado.length}
                  onPrevPage={() => setPaginaTopDeuda((prev) => Math.max(1, prev - 1))}
                  onNextPage={() => setPaginaTopDeuda((prev) => Math.min(totalPaginasTopDeuda, prev + 1))}
                  panelHeight={desktopColumnHeight}
                  onOpenProveedor={handleOpenFicha}
                />
              </View>
            </View>
          ) : (
            <>
              <PendientesTable
                items={pendientesPaginados}
                page={paginaPendientes}
                totalPages={totalPaginasPendientes}
                totalItems={pendientesOrdenados.length}
                onPrevPage={() => setPaginaPendientes((prev) => Math.max(1, prev - 1))}
                onNextPage={() => setPaginaPendientes((prev) => Math.min(totalPaginasPendientes, prev + 1))}
                sucursales={sucursalesPermitidas}
                onOpenProveedor={handleOpenFicha}
              />
            </>
          )}

          {isDesktop ? (
            <View className="mb-3 rounded-2xl border px-4 py-3" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}>
              <Text className="text-xs font-semibold" style={{ color: stitchColors.secondary }}>
                Tendencia mensual de deuda
              </Text>
              {trendRows.length === 0 ? (
                <Text className="mt-2 text-sm" style={{ color: stitchColors.onSurfaceVariant }}>
                  Sin datos de tendencia.
                </Text>
              ) : (
                <View className="mt-2" style={{ gap: 8 }}>
                  {trendRows.map((row, index) => {
                    const width: DimensionValue = maxTrend > 0 ? `${Math.max(8, Math.round((row.total / maxTrend) * 100))}%` : '0%';

                    return (
                      <View key={`tendencia-${row.label}-${index}`}>
                        <View className="mb-1 flex-row items-center justify-between">
                          <Text className="text-xs font-semibold" style={{ color: stitchColors.onSurfaceVariant }}>{row.label}</Text>
                          <Text className="text-xs font-semibold" style={{ color: stitchColors.onBackground }}>{formatCurrency(row.total)}</Text>
                        </View>
                        <View className="h-2 overflow-hidden rounded-full" style={{ backgroundColor: stitchColors.surfaceContainer }}>
                          <View className="h-full rounded-full" style={{ width, backgroundColor: '#0f766e' }} />
                        </View>
                      </View>
                    );
                  })}
                </View>
              )}
            </View>
          ) : (
            <>
              <TopDeudaList
                items={topDeudaPaginado}
                page={paginaTopDeuda}
                totalPages={totalPaginasTopDeuda}
                totalItems={topDeudaOrdenado.length}
                onPrevPage={() => setPaginaTopDeuda((prev) => Math.max(1, prev - 1))}
                onNextPage={() => setPaginaTopDeuda((prev) => Math.min(totalPaginasTopDeuda, prev + 1))}
                onOpenProveedor={handleOpenFicha}
              />

              <View className="mb-3 rounded-2xl border px-4 py-3" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}>
                <Text className="text-xs font-semibold" style={{ color: stitchColors.secondary }}>
                  Tendencia mensual de deuda
                </Text>
                {trendRows.length === 0 ? (
                  <Text className="mt-2 text-sm" style={{ color: stitchColors.onSurfaceVariant }}>
                    Sin datos de tendencia.
                  </Text>
                ) : (
                  <View className="mt-2" style={{ gap: 8 }}>
                    {trendRows.map((row, index) => {
                      const width: DimensionValue = maxTrend > 0 ? `${Math.max(8, Math.round((row.total / maxTrend) * 100))}%` : '0%';

                      return (
                        <View key={`tendencia-${row.label}-${index}`}>
                          <View className="mb-1 flex-row items-center justify-between">
                            <Text className="text-xs font-semibold" style={{ color: stitchColors.onSurfaceVariant }}>{row.label}</Text>
                            <Text className="text-xs font-semibold" style={{ color: stitchColors.onBackground }}>{formatCurrency(row.total)}</Text>
                          </View>
                          <View className="h-2 overflow-hidden rounded-full" style={{ backgroundColor: stitchColors.surfaceContainer }}>
                            <View className="h-full rounded-full" style={{ width, backgroundColor: '#0f766e' }} />
                          </View>
                        </View>
                      );
                    })}
                  </View>
                )}
              </View>
            </>
          )}
          </View>
        </ScrollView>
      )}
      </>
      )}

      <Modal
        visible={showSucursalSelector}
        transparent
        animationType="fade"
        onRequestClose={() => {
          setBusquedaSucursal('');
          setShowSucursalSelector(false);
        }}
      >
        <View className="flex-1 justify-center px-6" style={{ backgroundColor: 'rgba(0,0,0,0.32)' }}>
          <View className="rounded-2xl border px-4 py-4" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}>
            <View className="mb-3 flex-row items-center justify-between">
              <Text className="text-sm font-semibold" style={{ color: stitchColors.onBackground }}>
                Seleccionar sucursal
              </Text>
              <Pressable
                onPress={() => {
                  setBusquedaSucursal('');
                  setShowSucursalSelector(false);
                }}
              >
                <MaterialIcons name="close" size={20} color={stitchColors.onSurfaceVariant} />
              </Pressable>
            </View>

            <TextInput
              value={busquedaSucursal}
              onChangeText={setBusquedaSucursal}
              placeholder="Buscar por descripcion o codigo"
              className="mb-2 rounded-lg border px-3 py-2"
              style={{ borderColor: stitchColors.outlineVariant, color: stitchColors.onSurface }}
              autoCorrect={false}
            />

            <Pressable
              className="mb-1 flex-row items-center justify-between rounded-lg px-3 py-2.5"
              style={{ backgroundColor: !sucursal ? `${stitchColors.primary}18` : stitchColors.surfaceLowest }}
              onPress={() => {
                setSucursal('');
                setBusquedaSucursal('');
                setShowSucursalSelector(false);
              }}
            >
              <Text className="text-sm font-semibold" style={{ color: !sucursal ? stitchColors.primary : stitchColors.onSurface }}>
                Todas las sucursales
              </Text>
              {!sucursal ? <MaterialIcons name="check" size={16} color={stitchColors.primary} /> : null}
            </Pressable>

            <ScrollView className="mt-1" style={{ maxHeight: 300 }}>
              {sucursalesFiltradas.map((option) => {
                const code = (option.codigo_sucursal ?? '').trim();
                const description = (option.descripcion ?? '').trim() || code;
                const active = sucursal === code;
                return (
                  <Pressable
                    key={`sucursal-option-${code}`}
                    className="mb-1 flex-row items-center justify-between rounded-lg px-3 py-2.5"
                    style={{ backgroundColor: active ? `${stitchColors.primary}18` : stitchColors.surfaceLowest }}
                    onPress={() => {
                      setSucursal(code);
                      setBusquedaSucursal('');
                      setShowSucursalSelector(false);
                    }}
                  >
                    <Text className="text-sm font-semibold" style={{ color: active ? stitchColors.primary : stitchColors.onSurface }}>
                      {`${description} - ${code}`}
                    </Text>
                    {active ? <MaterialIcons name="check" size={16} color={stitchColors.primary} /> : null}
                  </Pressable>
                );
              })}

              {sucursalesPermitidas.length === 0 ? (
                <Text className="px-1 py-2 text-xs" style={{ color: stitchColors.onSurfaceVariant }}>
                  No hay sucursales disponibles para la empresa actual.
                </Text>
              ) : sucursalesFiltradas.length === 0 ? (
                <Text className="px-1 py-2 text-xs" style={{ color: stitchColors.onSurfaceVariant }}>
                  No hay sucursales que coincidan con la busqueda.
                </Text>
              ) : null}
            </ScrollView>
          </View>
        </View>
      </Modal>

    <Modal
      visible={showProveedorSelector}
      transparent
      animationType="fade"
      onRequestClose={() => {
        setBusquedaProveedor('');
        setShowProveedorSelector(false);
      }}
    >
      <View className="flex-1 justify-center px-6" style={{ backgroundColor: 'rgba(0,0,0,0.32)' }}>
        <View className="rounded-2xl border px-4 py-4" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}>
          <View className="mb-3 flex-row items-center justify-between">
            <Text className="text-sm font-semibold" style={{ color: stitchColors.onBackground }}>
              Seleccionar proveedor
            </Text>
            <Pressable onPress={() => { setBusquedaProveedor(''); setShowProveedorSelector(false); }}>
              <MaterialIcons name="close" size={20} color={stitchColors.onSurfaceVariant} />
            </Pressable>
          </View>

          <TextInput
            value={busquedaProveedor}
            onChangeText={setBusquedaProveedor}
            placeholder="Buscar por nombre o codigo"
            className="mb-2 rounded-lg border px-3 py-2"
            style={{ borderColor: stitchColors.outlineVariant, color: stitchColors.onSurface }}
            autoCorrect={false}
          />

          <Pressable
            className="mb-1 flex-row items-center justify-between rounded-lg px-3 py-2.5"
            style={{ backgroundColor: !codProveedorBusqueda.trim() ? `${stitchColors.primary}18` : stitchColors.surfaceLowest }}
            onPress={() => {
              setCodProveedorBusqueda('');
              setBusquedaProveedor('');
              setShowProveedorSelector(false);
            }}
          >
            <Text className="text-sm font-semibold" style={{ color: !codProveedorBusqueda.trim() ? stitchColors.primary : stitchColors.onSurface }}>
              Todos los proveedores
            </Text>
            {!codProveedorBusqueda.trim() ? <MaterialIcons name="check" size={16} color={stitchColors.primary} /> : null}
          </Pressable>

          <ScrollView className="mt-1" style={{ maxHeight: 300 }}>
            {proveedoresFiltrados.map((option) => {
              const active = codProveedorBusqueda.trim().toUpperCase() === option.codigo.toUpperCase();
              return (
                <Pressable
                  key={`proveedor-option-${option.codigo}`}
                  className="mb-1 flex-row items-center justify-between rounded-lg px-3 py-2.5"
                  style={{ backgroundColor: active ? `${stitchColors.primary}18` : stitchColors.surfaceLowest }}
                  onPress={() => {
                    setCodProveedorBusqueda(option.codigo);
                    setBusquedaProveedor('');
                    setShowProveedorSelector(false);
                  }}
                >
                  <Text className="text-sm font-semibold" style={{ color: active ? stitchColors.primary : stitchColors.onSurface }}>
                    {`${option.nombre || 'Sin nombre'} - ${option.codigo}`}
                  </Text>
                  {active ? <MaterialIcons name="check" size={16} color={stitchColors.primary} /> : null}
                </Pressable>
              );
            })}
            {proveedoresOpciones.length === 0 ? (
              <Text className="px-1 py-2 text-xs" style={{ color: stitchColors.onSurfaceVariant }}>
                No hay proveedores en el dashboard actual.
              </Text>
            ) : proveedoresFiltrados.length === 0 ? (
              <Text className="px-1 py-2 text-xs" style={{ color: stitchColors.onSurfaceVariant }}>
                No hay proveedores que coincidan con la busqueda.
              </Text>
            ) : null}
          </ScrollView>
        </View>
      </View>
    </Modal>

    <Modal
      visible={showStatusSelector}
      transparent
      animationType="fade"
        onRequestClose={() => setShowStatusSelector(false)}
      >
        <View className="flex-1 justify-center px-6" style={{ backgroundColor: 'rgba(0,0,0,0.32)' }}>
          <View className="rounded-2xl border px-4 py-4" style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}>
            <View className="mb-3 flex-row items-center justify-between">
              <Text className="text-sm font-semibold" style={{ color: stitchColors.onBackground }}>
                Seleccionar status de compra
              </Text>
              <Pressable onPress={() => setShowStatusSelector(false)}>
                <MaterialIcons name="close" size={20} color={stitchColors.onSurfaceVariant} />
              </Pressable>
            </View>

            <Pressable
              className="mb-1 flex-row items-center justify-between rounded-lg px-3 py-2.5"
              style={{ backgroundColor: !status.trim() ? `${stitchColors.secondary}18` : stitchColors.surfaceLowest }}
              onPress={() => {
                setStatus('');
                setShowStatusSelector(false);
              }}
            >
              <Text className="text-sm font-semibold" style={{ color: !status.trim() ? stitchColors.secondary : stitchColors.onSurface }}>
                Todos los status
              </Text>
              {!status.trim() ? <MaterialIcons name="check" size={16} color={stitchColors.secondary} /> : null}
            </Pressable>

            <ScrollView className="mt-1" style={{ maxHeight: 260 }}>
              {statusQuickOptions.map((option) => {
                const active = status.trim().toUpperCase() === option;
                return (
                  <Pressable
                    key={`status-option-${option}`}
                    className="mb-1 flex-row items-center justify-between rounded-lg px-3 py-2.5"
                    style={{ backgroundColor: active ? `${stitchColors.secondary}18` : stitchColors.surfaceLowest }}
                    onPress={() => {
                      setStatus(option);
                      setShowStatusSelector(false);
                    }}
                  >
                    <Text className="text-sm font-semibold" style={{ color: active ? stitchColors.secondary : stitchColors.onSurface }}>
                      {option}
                    </Text>
                    {active ? <MaterialIcons name="check" size={16} color={stitchColors.secondary} /> : null}
                  </Pressable>
                );
              })}
            </ScrollView>
          </View>
        </View>
      </Modal>

      <Modal
        visible={showFechaSelector}
        transparent
        animationType="fade"
        onRequestClose={() => setShowFechaSelector(false)}
      >
        <KeyboardAvoidingView
          className="flex-1 justify-center px-5"
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          style={{ backgroundColor: 'rgba(15, 23, 42, 0.32)' }}
        >
          <Pressable
            style={{ position: 'absolute', top: 0, right: 0, bottom: 0, left: 0 }}
            onPress={() => setShowFechaSelector(false)}
          />
          <View
            className="rounded-2xl border px-4 py-4"
            style={{
              borderColor: stitchColors.outlineVariant,
              backgroundColor: stitchColors.surfaceLowest,
              maxWidth: 420,
              width: '100%',
              alignSelf: 'center',
            }}
          >
            <View className="mb-3 flex-row items-center justify-between">
              <View>
                <Text className="text-sm font-semibold" style={{ color: stitchColors.onBackground }}>
                  Rango de fechas
                </Text>
                <Text className="mt-0.5 text-[10px]" style={{ color: stitchColors.onSurfaceVariant }}>
                  Escribe dd/mm/aaaa o elige en el calendario
                </Text>
              </View>
              <Pressable onPress={() => setShowFechaSelector(false)}>
                <MaterialIcons name="close" size={20} color={stitchColors.onSurfaceVariant} />
              </Pressable>
            </View>

            <View className="mb-3 flex-row" style={{ gap: 8 }}>
              <View
                className="flex-1 rounded-xl border px-3 py-2"
                style={{ borderColor: rangeStep === 'start' ? stitchColors.primary : stitchColors.outlineVariant }}
              >
                <Text className="text-[10px]" style={{ color: stitchColors.onSurfaceVariant }}>Desde</Text>
                <TextInput
                  value={draftFromText}
                  onChangeText={(value) => handleTypedDate('from', value)}
                  onFocus={() => setRangeStep('start')}
                  placeholder="dd/mm/aaaa"
                  placeholderTextColor={stitchColors.outline}
                  keyboardType="number-pad"
                  inputMode="numeric"
                  maxLength={10}
                  className="mt-0.5 p-0 text-xs font-semibold"
                  style={{ color: stitchColors.onSurface, minHeight: 20 }}
                />
              </View>
              <View
                className="flex-1 rounded-xl border px-3 py-2"
                style={{ borderColor: rangeStep === 'end' ? stitchColors.primary : stitchColors.outlineVariant }}
              >
                <Text className="text-[10px]" style={{ color: stitchColors.onSurfaceVariant }}>Hasta</Text>
                <TextInput
                  value={draftToText}
                  onChangeText={(value) => handleTypedDate('to', value)}
                  onFocus={() => setRangeStep('end')}
                  placeholder="dd/mm/aaaa"
                  placeholderTextColor={stitchColors.outline}
                  keyboardType="number-pad"
                  inputMode="numeric"
                  maxLength={10}
                  className="mt-0.5 p-0 text-xs font-semibold"
                  style={{ color: stitchColors.onSurface, minHeight: 20 }}
                />
              </View>
            </View>

            <View className="mb-3 flex-row items-center justify-between">
              <Pressable onPress={() => setCalendarCursor((prev) => shiftMonthDate(prev, -1))} className="rounded-lg px-2 py-1">
                <MaterialIcons name="chevron-left" size={20} color={stitchColors.onSurfaceVariant} />
              </Pressable>
              <Text className="text-xs font-semibold capitalize" style={{ color: stitchColors.onSurface }}>
                {calendarDays.label}
              </Text>
              <Pressable onPress={() => setCalendarCursor((prev) => shiftMonthDate(prev, 1))} className="rounded-lg px-2 py-1">
                <MaterialIcons name="chevron-right" size={20} color={stitchColors.onSurfaceVariant} />
              </Pressable>
            </View>

            <View className="mb-2 flex-row">
              {WEEK_DAYS.map((dayName) => (
                <Text
                  key={`weekday-${dayName}`}
                  className="text-[11px] font-semibold"
                  style={{ color: stitchColors.onSurfaceVariant, width: `${100 / 7}%`, textAlign: 'center' }}
                >
                  {dayName}
                </Text>
              ))}
            </View>

            <View className="flex-row flex-wrap">
              {calendarDays.cells.map((cell, idx) => {
                if (!cell) {
                  return <View key={`empty-${idx}`} style={{ width: `${100 / 7}%`, aspectRatio: 1, padding: 2 }} />;
                }

                const isStart = draftFrom === cell.iso;
                const isEnd = draftTo === cell.iso;
                const inRange = Boolean(draftFrom && draftTo && cell.iso >= draftFrom && cell.iso <= draftTo);
                const isToday = cell.iso === todayIso;
                const selected = isStart || isEnd;

                return (
                  <View key={cell.iso} style={{ width: `${100 / 7}%`, aspectRatio: 1, padding: 2 }}>
                    <Pressable
                      className="flex-1 items-center justify-center"
                      style={{
                        borderRadius: 14,
                        backgroundColor: selected
                          ? stitchColors.primary
                          : inRange
                            ? `${stitchColors.primary}22`
                            : stitchColors.surface,
                        borderWidth: selected ? 0 : isToday ? 1.5 : 1,
                        borderColor: selected
                          ? 'transparent'
                          : isToday
                            ? stitchColors.secondary
                            : stitchColors.outlineVariant,
                      }}
                      onPress={() => selectCalendarDay(cell.iso)}
                    >
                      <Text
                        className="text-[11px] font-semibold"
                        style={{
                          color: selected
                            ? stitchColors.onPrimary
                            : isToday
                              ? stitchColors.secondary
                              : stitchColors.onSurface,
                        }}
                      >
                        {cell.day}
                      </Text>
                    </Pressable>
                  </View>
                );
              })}
            </View>

            <View className="mt-3 flex-row flex-wrap" style={{ gap: 6 }}>
              {[
                { key: 'hoy' as const, label: 'Hoy' },
                { key: '7d' as const, label: '7 días' },
                { key: 'mes' as const, label: 'Este mes' },
                { key: '30d' as const, label: '30 días' },
              ].map((preset) => (
                <Pressable
                  key={preset.key}
                  className="rounded-full px-3 py-1.5"
                  style={{ backgroundColor: stitchColors.surface, borderWidth: 1, borderColor: stitchColors.outlineVariant }}
                  onPress={() => applyPreset(preset.key)}
                >
                  <Text className="text-[11px] font-semibold" style={{ color: stitchColors.onSurface }}>
                    {preset.label}
                  </Text>
                </Pressable>
              ))}
            </View>

            <View className="mt-3 flex-row" style={{ gap: 8 }}>
              <Pressable
                className="rounded-xl border px-3 py-2"
                style={{ borderColor: stitchColors.outlineVariant, backgroundColor: stitchColors.surface }}
                onPress={() => {
                  setDateFrom('');
                  setDateTo('');
                  setShowFechaSelector(false);
                }}
              >
                <Text className="text-xs font-semibold" style={{ color: stitchColors.onSurfaceVariant }}>
                  Limpiar
                </Text>
              </Pressable>
              <Pressable
                disabled={!draftFrom && !parseTypedDate(draftFromText)}
                className="flex-1 items-center rounded-xl px-3 py-2"
                style={{
                  backgroundColor: (draftFrom || parseTypedDate(draftFromText))
                    ? stitchColors.primary
                    : stitchColors.outlineVariant,
                }}
                onPress={() => {
                  const from = parseTypedDate(draftFromText) || draftFrom;
                  const to = parseTypedDate(draftToText) || draftTo || from;
                  if (!from) return;
                  applyDraftRange(from, to);
                }}
              >
                <Text className="text-xs font-semibold" style={{ color: stitchColors.onPrimary }}>
                  Aplicar
                </Text>
              </Pressable>
            </View>
          </View>
        </KeyboardAvoidingView>
      </Modal>
    </View>
  );
}
