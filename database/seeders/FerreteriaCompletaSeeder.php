<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class FerreteriaCompletaSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. CATEGORÍAS (Clasificadas tipo ferretería)
        // ==========================================
        $categoriasData = [
            'Herramientas Manuales',
            'Herramientas Eléctricas',
            'Materiales de Construcción',
            'Pinturas e Impermeabilizantes',
            'Plomería y Tubería',
            'Electricidad y Cableado',
            'Cerrajería y Seguridad',
        ];

        $cats = [];
        foreach ($categoriasData as $nombreCat) {
            $cats[$nombreCat] = Categoria::firstOrCreate(['nombre' => $nombreCat]);
        }

        // ==========================================
        // 2. PROVEEDORES (20 Registros)
        // ==========================================
        $proveedoresNombres = [
            'Ferretería Global S.A.S.', 'Herramientas de Colombia', 'Comercializadora CementoAndino',
            'Pinturas Supertinte Ltda', 'Plásticos y Tubos del Sur', 'MegaEléctricos S.A.',
            'Cerrajería y Seguridad Total', 'Aceros y Mallas de Nariño', 'Distribuidora Ferretera Ipiales',
            'Importaciones y Herramientas Pro', 'Soluciones Constructivas S.A.', 'Químicos y Solventes del Norte',
            'Ferretería Industrial del Pacífico', 'Comercial Eléctrica Andina', 'Ferretería y Tuberías PVC S.A.',
            'Abrasivos y Discos de Colombia', 'Ferretería Mayorista El Diamante', 'Suministros para Obra Gris S.A.S.',
            'Cerrajería Moderna Ltda', 'Distribuciones Globales de Ferretería',
        ];

        $provs = [];
        foreach ($proveedoresNombres as $index => $nombre) {
            $nit = '900'.str_pad($index + 1, 6, '0', STR_PAD_LEFT).'-'.(($index % 9) + 1);
            $datosProv = [
                'nombre' => $nombre,
                'telefono' => '310'.rand(100, 999).rand(1000, 9999),
            ];

            if (Schema::hasColumn('proveedors', 'direccion')) {
                $datosProv['direccion'] = 'Calle '.rand(1, 50).' # '.rand(1, 99).' - '.rand(1, 99);
            }

            $provs[] = Proveedor::firstOrCreate(['nit' => $nit], $datosProv)->id;
        }

        // ==========================================
        // 3. CLIENTES (20 Registros)
        // ==========================================
        $clientesNombres = [
            'Carlos Pérez', 'Ana María Gómez', 'Luis Fernando Riascos', 'Diana Lucía Benavides',
            'Juan Carlos Moncayo', 'Maritza Ovallos', 'Andrés Felipe Pantoja', 'Sofía Alejandra Haro',
            'Miguel Ángel Rosero', 'Rosa Elena Jojoa', 'Jorge Iván Bastidas', 'Paola Andrea Zúñiga',
            'Gabriel Eduardo Chaves', 'Natalia Estefanía Viteri', 'Héctor Mauricio Caicedo',
            'Claudia Marcela Insuasty', 'Mauricio Alejandro Sotomayor', 'Yolanda del Carmen Portilla',
            'Diego Armando Jaramillo', 'Sandra Milena Villota',
        ];

        $clientesIds = [];
        foreach ($clientesNombres as $index => $nombre) {
            $doc = '1085'.str_pad($index + 100, 6, '0', STR_PAD_LEFT);
            $datosCliente = [
                'nombre' => $nombre,
                'cedula_nit' => $doc,
                'telefono' => '320'.rand(100, 999).rand(1000, 9999),
            ];

            if (Schema::hasColumn('clientes', 'direccion')) {
                $datosCliente['direccion'] = 'Carrera '.rand(1, 40).' # '.rand(1, 99);
            }
            if (Schema::hasColumn('clientes', 'email')) {
                $datosCliente['email'] = strtolower(explode(' ', $nombre)[0]).($index + 10).'@gmail.com';
            }

            $clientesIds[] = Cliente::firstOrCreate(['cedula_nit' => $doc], $datosCliente)->id;
        }

        // ==========================================
        // 4. PRODUCTOS (21 Productos clasificados)
        // ==========================================
        $productosData = [
            // Herramientas Manuales
            ['nombre' => 'Martillo de Uña Acero Forjado 16oz', 'sku' => 'HERM-001', 'cat' => 'Herramientas Manuales', 'p_compra' => 22000, 'p_venta' => 38000, 'stock' => 25],
            ['nombre' => 'Juego de Destornilladores x6 Pzs', 'sku' => 'HERM-002', 'cat' => 'Herramientas Manuales', 'p_compra' => 18000, 'p_venta' => 32000, 'stock' => 15],
            ['nombre' => 'Alicate Universal 8 Pulgadas', 'sku' => 'HERM-003', 'cat' => 'Herramientas Manuales', 'p_compra' => 15000, 'p_venta' => 26000, 'stock' => 20],
            ['nombre' => 'Cinta Métrica Flexómetro 5m', 'sku' => 'HERM-004', 'cat' => 'Herramientas Manuales', 'p_compra' => 8000, 'p_venta' => 15000, 'stock' => 40],

            // Herramientas Eléctricas
            ['nombre' => 'Taladro Percutor 650W Profesional', 'sku' => 'ELEC-001', 'cat' => 'Herramientas Eléctricas', 'p_compra' => 140000, 'p_venta' => 195000, 'stock' => 10],
            ['nombre' => 'Pulidora Angular 4-1/2 750W', 'sku' => 'ELEC-002', 'cat' => 'Herramientas Eléctricas', 'p_compra' => 160000, 'p_venta' => 230000, 'stock' => 8],
            ['nombre' => 'Sierra Circular 7-1/4 1400W', 'sku' => 'ELEC-003', 'cat' => 'Herramientas Eléctricas', 'p_compra' => 250000, 'p_venta' => 349000, 'stock' => 5],

            // Materiales de Construcción
            ['nombre' => 'Bolsa de Cemento Gris 50kg', 'sku' => 'CONS-001', 'cat' => 'Materiales de Construcción', 'p_compra' => 28000, 'p_venta' => 34000, 'stock' => 60],
            ['nombre' => 'Varilla Corrugada 3/8 Pulgada', 'sku' => 'CONS-002', 'cat' => 'Materiales de Construcción', 'p_compra' => 24000, 'p_venta' => 31000, 'stock' => 100],
            ['nombre' => 'Metro de Arena de Peña', 'sku' => 'CONS-003', 'cat' => 'Materiales de Construcción', 'p_compra' => 45000, 'p_venta' => 60000, 'stock' => 30],

            // Pinturas e Impermeabilizantes
            ['nombre' => 'Pintura Vinilo TIPO 1 Galón Blanco', 'sku' => 'PINT-001', 'cat' => 'Pinturas e Impermeabilizantes', 'p_compra' => 45000, 'p_venta' => 65000, 'stock' => 18],
            ['nombre' => 'Esmalte Sintético 1/4 Galón Negro', 'sku' => 'PINT-002', 'cat' => 'Pinturas e Impermeabilizantes', 'p_compra' => 18000, 'p_venta' => 28000, 'stock' => 22],
            ['nombre' => 'Impermeabilizante Acrílico 5 Galones', 'sku' => 'PINT-003', 'cat' => 'Pinturas e Impermeabilizantes', 'p_compra' => 180000, 'p_venta' => 245000, 'stock' => 7],

            // Plomería y Tubería
            ['nombre' => 'Tubo PVC Presión 1/2 Pulgada', 'sku' => 'PLOM-001', 'cat' => 'Plomería y Tubería', 'p_compra' => 7000, 'p_venta' => 12000, 'stock' => 45],
            ['nombre' => 'Pegante para PVC Tarro 1/4', 'sku' => 'PLOM-002', 'cat' => 'Plomería y Tubería', 'p_compra' => 9000, 'p_venta' => 16000, 'stock' => 25],
            ['nombre' => 'Llave de Paso PVC 1/2 Pulgada', 'sku' => 'PLOM-003', 'cat' => 'Plomería y Tubería', 'p_compra' => 4000, 'p_venta' => 8500, 'stock' => 30],

            // Electricidad y Cableado
            ['nombre' => 'Rollo de Cable Eléctrico #12 (100m)', 'sku' => 'ELET-001', 'cat' => 'Electricidad y Cableado', 'p_compra' => 110000, 'p_venta' => 155000, 'stock' => 12],
            ['nombre' => 'Interruptor Doble con Placa', 'sku' => 'ELET-002', 'cat' => 'Electricidad y Cableado', 'p_compra' => 6000, 'p_venta' => 11000, 'stock' => 35],
            ['nombre' => 'Bombillo LED Ahorrador 12W', 'sku' => 'ELET-003', 'cat' => 'Electricidad y Cableado', 'p_compra' => 5000, 'p_venta' => 10000, 'stock' => 50],

            // Cerrajería y Seguridad
            ['nombre' => 'Candado de Bronce 50mm Alta Seguridad', 'sku' => 'CERR-001', 'cat' => 'Cerrajería y Seguridad', 'p_compra' => 21000, 'p_venta' => 35000, 'stock' => 20],
            ['nombre' => 'Cerradura de Sobreponer para Puerta', 'sku' => 'CERR-002', 'cat' => 'Cerrajería y Seguridad', 'p_compra' => 42000, 'p_venta' => 64000, 'stock' => 14],

            // Catálogo adicional: 10 SKU nuevos por cada categoría.
            ['nombre' => 'Candado laminado de 40 mm', 'sku' => 'FCOM-CER-001', 'cat' => 'Cerrajería y Seguridad', 'p_compra' => 12000, 'p_venta' => 16000, 'stock' => 24],
            ['nombre' => 'Candado laminado de 50 mm', 'sku' => 'FCOM-CER-002', 'cat' => 'Cerrajería y Seguridad', 'p_compra' => 18000, 'p_venta' => 24000, 'stock' => 18],
            ['nombre' => 'Cerradura cilíndrica para alcoba', 'sku' => 'FCOM-CER-003', 'cat' => 'Cerrajería y Seguridad', 'p_compra' => 32000, 'p_venta' => 42000, 'stock' => 12],
            ['nombre' => 'Cerradura cilíndrica para entrada principal', 'sku' => 'FCOM-CER-004', 'cat' => 'Cerrajería y Seguridad', 'p_compra' => 48000, 'p_venta' => 62000, 'stock' => 10],
            ['nombre' => 'Cerrojo de seguridad con llave', 'sku' => 'FCOM-CER-005', 'cat' => 'Cerrajería y Seguridad', 'p_compra' => 26000, 'p_venta' => 34000, 'stock' => 14],
            ['nombre' => 'Pasador metálico galvanizado de 4 pulgadas', 'sku' => 'FCOM-CER-006', 'cat' => 'Cerrajería y Seguridad', 'p_compra' => 8000, 'p_venta' => 11000, 'stock' => 30],
            ['nombre' => 'Bisagra de acero para puerta 3 pulgadas par', 'sku' => 'FCOM-CER-007', 'cat' => 'Cerrajería y Seguridad', 'p_compra' => 9000, 'p_venta' => 12000, 'stock' => 26],
            ['nombre' => 'Manija metálica para puerta', 'sku' => 'FCOM-CER-008', 'cat' => 'Cerrajería y Seguridad', 'p_compra' => 22000, 'p_venta' => 29000, 'stock' => 16],
            ['nombre' => 'Cilindro de repuesto para cerradura', 'sku' => 'FCOM-CER-009', 'cat' => 'Cerrajería y Seguridad', 'p_compra' => 18000, 'p_venta' => 24000, 'stock' => 15],
            ['nombre' => 'Cadena galvanizada por metro', 'sku' => 'FCOM-CER-010', 'cat' => 'Cerrajería y Seguridad', 'p_compra' => 14000, 'p_venta' => 18500, 'stock' => 20],

            ['nombre' => 'Cable de cobre THHN calibre 12 por metro', 'sku' => 'FCOM-ELE-001', 'cat' => 'Electricidad y Cableado', 'p_compra' => 1800, 'p_venta' => 2500, 'stock' => 120],
            ['nombre' => 'Cable de cobre THHN calibre 14 por metro', 'sku' => 'FCOM-ELE-002', 'cat' => 'Electricidad y Cableado', 'p_compra' => 1300, 'p_venta' => 1800, 'stock' => 140],
            ['nombre' => 'Tomacorriente doble con polo a tierra', 'sku' => 'FCOM-ELE-003', 'cat' => 'Electricidad y Cableado', 'p_compra' => 12000, 'p_venta' => 16000, 'stock' => 25],
            ['nombre' => 'Interruptor sencillo con placa', 'sku' => 'FCOM-ELE-004', 'cat' => 'Electricidad y Cableado', 'p_compra' => 6500, 'p_venta' => 9000, 'stock' => 32],
            ['nombre' => 'Interruptor doble con placa', 'sku' => 'FCOM-ELE-005', 'cat' => 'Electricidad y Cableado', 'p_compra' => 9500, 'p_venta' => 13000, 'stock' => 24],
            ['nombre' => 'Bombillo LED luz blanca 12 W', 'sku' => 'FCOM-ELE-006', 'cat' => 'Electricidad y Cableado', 'p_compra' => 7000, 'p_venta' => 9500, 'stock' => 40],
            ['nombre' => 'Reflector LED exterior 50 W', 'sku' => 'FCOM-ELE-007', 'cat' => 'Electricidad y Cableado', 'p_compra' => 42000, 'p_venta' => 55000, 'stock' => 12],
            ['nombre' => 'Breaker monopolar de 20 A', 'sku' => 'FCOM-ELE-008', 'cat' => 'Electricidad y Cableado', 'p_compra' => 15000, 'p_venta' => 20000, 'stock' => 18],
            ['nombre' => 'Cinta aislante eléctrica negra', 'sku' => 'FCOM-ELE-009', 'cat' => 'Electricidad y Cableado', 'p_compra' => 2500, 'p_venta' => 3500, 'stock' => 60],
            ['nombre' => 'Extensión eléctrica encauchetada 10 metros', 'sku' => 'FCOM-ELE-010', 'cat' => 'Electricidad y Cableado', 'p_compra' => 38000, 'p_venta' => 50000, 'stock' => 14],

            ['nombre' => 'Taladro percutor de 650 W', 'sku' => 'FCOM-HE-001', 'cat' => 'Herramientas Eléctricas', 'p_compra' => 145000, 'p_venta' => 188000, 'stock' => 8],
            ['nombre' => 'Pulidora angular de 4 1/2 pulgadas 750 W', 'sku' => 'FCOM-HE-002', 'cat' => 'Herramientas Eléctricas', 'p_compra' => 165000, 'p_venta' => 214000, 'stock' => 7],
            ['nombre' => 'Sierra circular eléctrica de 7 1/4 pulgadas', 'sku' => 'FCOM-HE-003', 'cat' => 'Herramientas Eléctricas', 'p_compra' => 250000, 'p_venta' => 325000, 'stock' => 5],
            ['nombre' => 'Lijadora orbital eléctrica de 240 W', 'sku' => 'FCOM-HE-004', 'cat' => 'Herramientas Eléctricas', 'p_compra' => 110000, 'p_venta' => 143000, 'stock' => 6],
            ['nombre' => 'Rotomartillo SDS de 800 W', 'sku' => 'FCOM-HE-005', 'cat' => 'Herramientas Eléctricas', 'p_compra' => 280000, 'p_venta' => 364000, 'stock' => 4],
            ['nombre' => 'Caladora eléctrica de 500 W', 'sku' => 'FCOM-HE-006', 'cat' => 'Herramientas Eléctricas', 'p_compra' => 135000, 'p_venta' => 175000, 'stock' => 5],
            ['nombre' => 'Atornillador inalámbrico de 12 V', 'sku' => 'FCOM-HE-007', 'cat' => 'Herramientas Eléctricas', 'p_compra' => 190000, 'p_venta' => 247000, 'stock' => 4],
            ['nombre' => 'Esmeril de banco de 6 pulgadas', 'sku' => 'FCOM-HE-008', 'cat' => 'Herramientas Eléctricas', 'p_compra' => 220000, 'p_venta' => 286000, 'stock' => 3],
            ['nombre' => 'Pistola de calor eléctrica de 2000 W', 'sku' => 'FCOM-HE-009', 'cat' => 'Herramientas Eléctricas', 'p_compra' => 95000, 'p_venta' => 123000, 'stock' => 5],
            ['nombre' => 'Compresor portátil de aire de 24 litros', 'sku' => 'FCOM-HE-010', 'cat' => 'Herramientas Eléctricas', 'p_compra' => 420000, 'p_venta' => 546000, 'stock' => 2],

            ['nombre' => 'Alicate universal de 8 pulgadas', 'sku' => 'FCOM-HM-001', 'cat' => 'Herramientas Manuales', 'p_compra' => 18000, 'p_venta' => 24000, 'stock' => 25],
            ['nombre' => 'Juego de destornilladores de 6 piezas', 'sku' => 'FCOM-HM-002', 'cat' => 'Herramientas Manuales', 'p_compra' => 30000, 'p_venta' => 39000, 'stock' => 16],
            ['nombre' => 'Llave ajustable cromada de 10 pulgadas', 'sku' => 'FCOM-HM-003', 'cat' => 'Herramientas Manuales', 'p_compra' => 26000, 'p_venta' => 35000, 'stock' => 18],
            ['nombre' => 'Flexómetro resistente de 5 metros', 'sku' => 'FCOM-HM-004', 'cat' => 'Herramientas Manuales', 'p_compra' => 15000, 'p_venta' => 19500, 'stock' => 32],
            ['nombre' => 'Nivel de aluminio de 24 pulgadas', 'sku' => 'FCOM-HM-005', 'cat' => 'Herramientas Manuales', 'p_compra' => 22000, 'p_venta' => 29000, 'stock' => 14],
            ['nombre' => 'Serrucho manual de 20 pulgadas', 'sku' => 'FCOM-HM-006', 'cat' => 'Herramientas Manuales', 'p_compra' => 24000, 'p_venta' => 32000, 'stock' => 12],
            ['nombre' => 'Formón para madera de 1 pulgada', 'sku' => 'FCOM-HM-007', 'cat' => 'Herramientas Manuales', 'p_compra' => 16000, 'p_venta' => 21000, 'stock' => 15],
            ['nombre' => 'Llave para tubo de 14 pulgadas', 'sku' => 'FCOM-HM-008', 'cat' => 'Herramientas Manuales', 'p_compra' => 42000, 'p_venta' => 55000, 'stock' => 8],
            ['nombre' => 'Arco de segueta metálico', 'sku' => 'FCOM-HM-009', 'cat' => 'Herramientas Manuales', 'p_compra' => 18000, 'p_venta' => 24000, 'stock' => 11],
            ['nombre' => 'Prensa tipo C de 4 pulgadas', 'sku' => 'FCOM-HM-010', 'cat' => 'Herramientas Manuales', 'p_compra' => 19000, 'p_venta' => 25000, 'stock' => 10],

            ['nombre' => 'Bulto de cemento gris de 50 kg', 'sku' => 'FCOM-MC-001', 'cat' => 'Materiales de Construcción', 'p_compra' => 25000, 'p_venta' => 32000, 'stock' => 80],
            ['nombre' => 'Bulto de mortero seco para pega de 25 kg', 'sku' => 'FCOM-MC-002', 'cat' => 'Materiales de Construcción', 'p_compra' => 16000, 'p_venta' => 21000, 'stock' => 42],
            ['nombre' => 'Varilla corrugada de 3/8 de pulgada', 'sku' => 'FCOM-MC-003', 'cat' => 'Materiales de Construcción', 'p_compra' => 24000, 'p_venta' => 31000, 'stock' => 55],
            ['nombre' => 'Ladrillo común de arcilla unidad', 'sku' => 'FCOM-MC-004', 'cat' => 'Materiales de Construcción', 'p_compra' => 800, 'p_venta' => 1100, 'stock' => 300],
            ['nombre' => 'Malla electrosoldada para placa 2 x 3 metros', 'sku' => 'FCOM-MC-005', 'cat' => 'Materiales de Construcción', 'p_compra' => 52000, 'p_venta' => 68000, 'stock' => 16],
            ['nombre' => 'Bulto de estuco plástico de 25 kg', 'sku' => 'FCOM-MC-006', 'cat' => 'Materiales de Construcción', 'p_compra' => 38000, 'p_venta' => 50000, 'stock' => 22],
            ['nombre' => 'Arena lavada en bulto', 'sku' => 'FCOM-MC-007', 'cat' => 'Materiales de Construcción', 'p_compra' => 12000, 'p_venta' => 16000, 'stock' => 45],
            ['nombre' => 'Gravilla en bulto para construcción', 'sku' => 'FCOM-MC-008', 'cat' => 'Materiales de Construcción', 'p_compra' => 14000, 'p_venta' => 18500, 'stock' => 38],
            ['nombre' => 'Bloque de concreto de 12 cm', 'sku' => 'FCOM-MC-009', 'cat' => 'Materiales de Construcción', 'p_compra' => 2800, 'p_venta' => 3800, 'stock' => 160],
            ['nombre' => 'Alambre negro recocido por kilo', 'sku' => 'FCOM-MC-010', 'cat' => 'Materiales de Construcción', 'p_compra' => 6500, 'p_venta' => 9000, 'stock' => 30],

            ['nombre' => 'Pintura vinilo tipo 1 blanca galón', 'sku' => 'FCOM-PIN-001', 'cat' => 'Pinturas e Impermeabilizantes', 'p_compra' => 62000, 'p_venta' => 80000, 'stock' => 18],
            ['nombre' => 'Esmalte sintético negro cuarto', 'sku' => 'FCOM-PIN-002', 'cat' => 'Pinturas e Impermeabilizantes', 'p_compra' => 18000, 'p_venta' => 24000, 'stock' => 22],
            ['nombre' => 'Impermeabilizante acrílico blanco galón', 'sku' => 'FCOM-PIN-003', 'cat' => 'Pinturas e Impermeabilizantes', 'p_compra' => 28000, 'p_venta' => 37000, 'stock' => 12],
            ['nombre' => 'Pintura anticorrosiva gris cuarto', 'sku' => 'FCOM-PIN-004', 'cat' => 'Pinturas e Impermeabilizantes', 'p_compra' => 22000, 'p_venta' => 29000, 'stock' => 14],
            ['nombre' => 'Sellador para muros galón', 'sku' => 'FCOM-PIN-005', 'cat' => 'Pinturas e Impermeabilizantes', 'p_compra' => 35000, 'p_venta' => 46000, 'stock' => 10],
            ['nombre' => 'Brocha profesional de 3 pulgadas', 'sku' => 'FCOM-PIN-006', 'cat' => 'Pinturas e Impermeabilizantes', 'p_compra' => 15500, 'p_venta' => 20500, 'stock' => 25],
            ['nombre' => 'Rodillo para pintura de 9 pulgadas', 'sku' => 'FCOM-PIN-007', 'cat' => 'Pinturas e Impermeabilizantes', 'p_compra' => 22000, 'p_venta' => 29000, 'stock' => 18],
            ['nombre' => 'Masilla acrílica para interiores kilo', 'sku' => 'FCOM-PIN-008', 'cat' => 'Pinturas e Impermeabilizantes', 'p_compra' => 9000, 'p_venta' => 12000, 'stock' => 28],
            ['nombre' => 'Thinner corriente galón', 'sku' => 'FCOM-PIN-009', 'cat' => 'Pinturas e Impermeabilizantes', 'p_compra' => 28000, 'p_venta' => 37000, 'stock' => 14],
            ['nombre' => 'Lija para madera grano 120 unidad', 'sku' => 'FCOM-PIN-010', 'cat' => 'Pinturas e Impermeabilizantes', 'p_compra' => 900, 'p_venta' => 1300, 'stock' => 80],

            ['nombre' => 'Tubo PVC presión de 1/2 pulgada x 6 metros', 'sku' => 'FCOM-PLO-001', 'cat' => 'Plomería y Tubería', 'p_compra' => 22000, 'p_venta' => 29000, 'stock' => 32],
            ['nombre' => 'Tubo PVC sanitario de 2 pulgadas x 6 metros', 'sku' => 'FCOM-PLO-002', 'cat' => 'Plomería y Tubería', 'p_compra' => 28000, 'p_venta' => 37000, 'stock' => 25],
            ['nombre' => 'Codo PVC presión de 1/2 pulgada', 'sku' => 'FCOM-PLO-003', 'cat' => 'Plomería y Tubería', 'p_compra' => 900, 'p_venta' => 1300, 'stock' => 100],
            ['nombre' => 'Tee PVC presión de 1/2 pulgada', 'sku' => 'FCOM-PLO-004', 'cat' => 'Plomería y Tubería', 'p_compra' => 1200, 'p_venta' => 1600, 'stock' => 90],
            ['nombre' => 'Válvula de bola PVC de 1/2 pulgada', 'sku' => 'FCOM-PLO-005', 'cat' => 'Plomería y Tubería', 'p_compra' => 12000, 'p_venta' => 16000, 'stock' => 18],
            ['nombre' => 'Grifería sencilla para lavamanos', 'sku' => 'FCOM-PLO-006', 'cat' => 'Plomería y Tubería', 'p_compra' => 38000, 'p_venta' => 50000, 'stock' => 12],
            ['nombre' => 'Sifón flexible para lavaplatos', 'sku' => 'FCOM-PLO-007', 'cat' => 'Plomería y Tubería', 'p_compra' => 18000, 'p_venta' => 24000, 'stock' => 16],
            ['nombre' => 'Pegante para PVC tarro de 1/4', 'sku' => 'FCOM-PLO-008', 'cat' => 'Plomería y Tubería', 'p_compra' => 9000, 'p_venta' => 12000, 'stock' => 24],
            ['nombre' => 'Cinta teflón para sellado de roscas', 'sku' => 'FCOM-PLO-009', 'cat' => 'Plomería y Tubería', 'p_compra' => 1800, 'p_venta' => 2500, 'stock' => 70],
            ['nombre' => 'Adaptador PVC presión de 1/2 pulgada', 'sku' => 'FCOM-PLO-010', 'cat' => 'Plomería y Tubería', 'p_compra' => 700, 'p_venta' => 1000, 'stock' => 110],
        ];

        $productosIds = [];
        foreach ($productosData as $p) {
            $prod = Producto::firstOrCreate(['codigo_sku' => $p['sku']], [
                'nombre' => $p['nombre'],
                'categoria_id' => $cats[$p['cat']]->id,
                'proveedor_id' => $provs[array_rand($provs)],
                'stock' => $p['stock'],
                'precio_compra' => $p['p_compra'],
                'precio_venta' => $p['p_venta'],
            ]);
            $productosIds[] = $prod;
        }

        // ==========================================
        // 5. HISTORIAL DE VENTAS (Con usuario automático)
        // ==========================================
        if (Venta::count() === 0 && count($productosIds) > 0) {
            $user = User::first() ?? User::create([
                'name' => 'Administrador',
                'email' => 'admin@ferreteria.com',
                'password' => bcrypt('password'),
            ]);

            for ($i = 1; $i <= 10; $i++) {
                $productoElegido = $productosIds[array_rand($productosIds)];
                $cantidad = rand(1, 3);
                $subtotal = $productoElegido->precio_venta * $cantidad;

                $datosVenta = [
                    'cliente_id' => $clientesIds[array_rand($clientesIds)],
                    'total' => $subtotal,
                    'created_at' => now()->subDays(rand(0, 10)),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('ventas', 'user_id')) {
                    $datosVenta['user_id'] = $user->id;
                } elseif (Schema::hasColumn('ventas', 'usuario_id')) {
                    $datosVenta['usuario_id'] = $user->id;
                }

                if (Schema::hasColumn('ventas', 'metodo_pago')) {
                    $datosVenta['metodo_pago'] = ['Efectivo', 'Tarjeta', 'Transferencia'][rand(0, 2)];
                }

                $venta = Venta::create($datosVenta);

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $productoElegido->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $productoElegido->precio_venta,
                    'subtotal' => $subtotal,
                ]);
            }
        }
    }
}
