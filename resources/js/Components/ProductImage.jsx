import React, { useState } from 'react';
import {
    Building2,
    Droplet,
    Hammer,
    HardHat,
    Layers,
    Lock,
    Paintbrush,
    ShieldCheck,
    Wrench,
    Zap,
} from 'lucide-react';
import { getProductImage } from '../Utils/productImage';

/**
 * Mapeo de categorías a iconos técnicos y paletas de color de ferretería.
 */
function getCategoryDesign(categoryName = '', productName = '') {
    const text = `${categoryName} ${productName}`.toLowerCase();

    if (text.includes('eléctrica') || text.includes('electrica') || text.includes('taladro') || text.includes('pulidora') || text.includes('sierra')) {
        return {
            Icon: Zap,
            label: 'Herramientas Eléctricas',
            badgeBg: 'bg-orange-500/10 border-orange-500/30 text-orange-400',
            iconColor: 'text-orange-400',
            glowColor: 'bg-orange-500/10',
        };
    }

    if (text.includes('cable') || text.includes('electricidad') || text.includes('tomacorriente') || text.includes('interruptor') || text.includes('breaker') || text.includes('bombillo')) {
        return {
            Icon: Zap,
            label: 'Electricidad y Cableado',
            badgeBg: 'bg-yellow-500/10 border-yellow-500/30 text-yellow-400',
            iconColor: 'text-yellow-400',
            glowColor: 'bg-yellow-500/10',
        };
    }

    if (text.includes('manual') || text.includes('martillo') || text.includes('alicate') || text.includes('destornillador') || text.includes('llave') || text.includes('flexometro')) {
        return {
            Icon: Hammer,
            label: 'Herramientas Manuales',
            badgeBg: 'bg-amber-500/10 border-amber-500/30 text-amber-400',
            iconColor: 'text-amber-400',
            glowColor: 'bg-amber-500/10',
        };
    }

    if (text.includes('construccion') || text.includes('construcción') || text.includes('cemento') || text.includes('arena') || text.includes('varilla') || text.includes('ladrillo') || text.includes('cal')) {
        return {
            Icon: HardHat,
            label: 'Materiales Construcción',
            badgeBg: 'bg-stone-500/10 border-stone-500/30 text-stone-300',
            iconColor: 'text-stone-300',
            glowColor: 'bg-stone-500/10',
        };
    }

    if (text.includes('pintura') || text.includes('esmalte') || text.includes('brocha') || text.includes('rodillo') || text.includes('impermeabilizante') || text.includes('thinner')) {
        return {
            Icon: Paintbrush,
            label: 'Pinturas y Acabados',
            badgeBg: 'bg-cyan-500/10 border-cyan-500/30 text-cyan-400',
            iconColor: 'text-cyan-400',
            glowColor: 'bg-cyan-500/10',
        };
    }

    if (text.includes('plomeria') || text.includes('plomería') || text.includes('tubo') || text.includes('pvc') || text.includes('sifon') || text.includes('valvula') || text.includes('pegante pvc')) {
        return {
            Icon: Droplet,
            label: 'Plomería y Tubería',
            badgeBg: 'bg-teal-500/10 border-teal-500/30 text-teal-400',
            iconColor: 'text-teal-400',
            glowColor: 'bg-teal-500/10',
        };
    }

    if (text.includes('cerrajeria') || text.includes('cerrajería') || text.includes('candado') || text.includes('cerradura') || text.includes('cerrojo') || text.includes('bisagra') || text.includes('pasador')) {
        return {
            Icon: ShieldCheck,
            label: 'Cerrajería y Seguridad',
            badgeBg: 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400',
            iconColor: 'text-emerald-400',
            glowColor: 'bg-emerald-500/10',
        };
    }

    return {
        Icon: Wrench,
        label: categoryName || 'Ferretería General',
        badgeBg: 'bg-slate-800 border-slate-700 text-slate-300',
        iconColor: 'text-amber-400',
        glowColor: 'bg-amber-500/10',
    };
}

/**
 * Componente unificado para renderizar imágenes de productos en el sistema.
 * Si el producto tiene una imagen válida asignada, la muestra.
 * Si no tiene imagen o la imagen falla (404/error de red), muestra un contenedor técnico profesional.
 */
export default function ProductImage({
    product,
    className = 'size-full',
    imgClassName = 'size-full object-cover',
    compact = false,
    showBadge = true,
}) {
    const [hasError, setHasError] = useState(false);
    const rawSrc = getProductImage(product);

    const categoryName = typeof product?.categoria === 'string'
        ? product.categoria
        : product?.categoria?.nombre || '';

    const design = getCategoryDesign(categoryName, product?.nombre || '');
    const { Icon } = design;

    if (rawSrc && !hasError) {
        return (
            <img
                src={rawSrc}
                alt={product?.nombre || 'Producto'}
                loading="lazy"
                onError={() => setHasError(true)}
                className={imgClassName}
            />
        );
    }

    // Placeholder técnico profesional
    if (compact) {
        return (
            <div className={`flex items-center justify-center bg-slate-900 border border-slate-800 ${className}`}>
                <Icon size={18} className={design.iconColor} aria-hidden="true" />
            </div>
        );
    }

    return (
        <div
            className={`relative flex flex-col items-center justify-center overflow-hidden bg-gradient-to-b from-slate-900 via-slate-950 to-slate-900 select-none ${className}`}
        >
            {/* Patrón técnico de cuadrícula tenue */}
            <div
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 opacity-[0.07]"
                style={{
                    backgroundImage: `radial-gradient(circle at 1px 1px, #ffffff 1px, transparent 0)`,
                    backgroundSize: '16px 16px',
                }}
            />

            {/* Resplandor suave del color temático */}
            <div
                aria-hidden="true"
                className={`pointer-events-none absolute size-20 rounded-full blur-2xl ${design.glowColor}`}
            />

            {/* Icono central de ferretería */}
            <div className="relative z-10 flex size-12 items-center justify-center rounded-2xl border border-slate-800 bg-slate-900/90 shadow-inner">
                <Icon size={24} className={design.iconColor} strokeWidth={1.8} aria-hidden="true" />
            </div>

            {/* Código SKU o Categoría Técnica */}
            {showBadge && (
                <div className="relative z-10 mt-2 text-center px-2">
                    {product?.codigo_sku && (
                        <p className="font-mono text-[10px] font-semibold tracking-wider text-slate-400">
                            {product.codigo_sku}
                        </p>
                    )}
                    <span
                        className={`inline-block mt-0.5 rounded-full border px-2 py-0.5 text-[9px] font-medium tracking-wide ${design.badgeBg}`}
                    >
                        {design.label}
                    </span>
                </div>
            )}
        </div>
    );
}
