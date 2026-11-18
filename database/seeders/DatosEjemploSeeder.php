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
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatosEjemploSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->orderBy('id')->first();

        if ($user === null) {
            throw new RuntimeException(
                'No hay usuarios registrados. Crea un usuario manualmente antes de generar ventas de ejemplo; este seeder no crea ni modifica usuarios.'
            );
        }

        DB::transaction(function () use ($user): void {
            $categories = $this->seedCategories();
            $suppliers = $this->seedSuppliers();
            $clients = $this->seedClients();
            $salesPlans = $this->salesPlans();
            $products = $this->seedProducts($categories, $suppliers, $salesPlans);

            $this->seedSales($user, $clients, $products, $salesPlans);
        });
    }

    /**
     * @return array<string, int>
     */
    private function seedCategories(): array
    {
        $names = [
            'Herramientas manuales',
            'Herramientas eléctricas',
            'Pinturas y acabados',
            'Plomería',
            'Electricidad',
            'Tornillería y fijación',
            'Cemento y construcción',
            'Seguridad industrial',
        ];

        $categories = [];

        foreach ($names as $name) {
            $category = Categoria::query()->firstOrCreate(
                ['nombre' => $name],
                [],
            );

            $categories[$name] = $category->id;
        }

        return $categories;
    }

    /**
     * @return array<string, int>
     */
    private function seedSuppliers(): array
    {
        $suppliers = [
            ['nit' => '901456780-1', 'nombre' => 'Suministros Andinos del Sur S.A.S.', 'telefono' => '3104567821', 'direccion' => 'Calle 17 # 6-42, Ipiales, Nariño'],
            ['nit' => '901456781-9', 'nombre' => 'Distribuciones La Frontera S.A.S.', 'telefono' => '3115678932', 'direccion' => 'Carrera 7 # 12-18, Ipiales, Nariño'],
            ['nit' => '901456782-7', 'nombre' => 'Pinturas y Acabados del Altiplano S.A.S.', 'telefono' => '3126789043', 'direccion' => 'Calle 12 # 8-35, Ipiales, Nariño'],
            ['nit' => '901456783-5', 'nombre' => 'Tuberías del Sur Colombiano S.A.S.', 'telefono' => '3137890154', 'direccion' => 'Carrera 6 # 20-16, Ipiales, Nariño'],
            ['nit' => '901456784-3', 'nombre' => 'Eléctricos Ipiales S.A.S.', 'telefono' => '3148901265', 'direccion' => 'Calle 14 # 5-27, Ipiales, Nariño'],
            ['nit' => '901456785-1', 'nombre' => 'Tornillos y Anclajes Nariño S.A.S.', 'telefono' => '3159012376', 'direccion' => 'Carrera 10 # 15-09, Ipiales, Nariño'],
            ['nit' => '901456786-0', 'nombre' => 'Materiales de Construcción Rumichaca S.A.S.', 'telefono' => '3160123487', 'direccion' => 'Vía Ipiales–Pupiales, sector El Placer, Nariño'],
            ['nit' => '901456787-8', 'nombre' => 'Protección Industrial Frontera S.A.S.', 'telefono' => '3171234598', 'direccion' => 'Calle 18 # 9-51, Ipiales, Nariño'],
        ];

        $supplierIds = [];

        foreach ($suppliers as $attributes) {
            $supplier = Proveedor::query()->firstOrCreate(
                ['nit' => $attributes['nit']],
                $attributes,
            );

            $supplierIds[$attributes['nit']] = $supplier->id;
        }

        return $supplierIds;
    }

    /**
     * @return array<string, int>
     */
    private function seedClients(): array
    {
        $clients = [
            ['cedula_nit' => '1085274101', 'nombre' => 'María Fernanda Rosero López', 'telefono' => '3004567812', 'direccion' => 'Barrio San Vicente, Ipiales, Nariño'],
            ['cedula_nit' => '1085274102', 'nombre' => 'Carlos Andrés Pantoja Muñoz', 'telefono' => '3015678923', 'direccion' => 'Barrio La Laguna, Ipiales, Nariño'],
            ['cedula_nit' => '1085274103', 'nombre' => 'Diana Carolina Insuasty Guerrero', 'telefono' => '3026789034', 'direccion' => 'Barrio Totoral, Ipiales, Nariño'],
            ['cedula_nit' => '1085274104', 'nombre' => 'Jorge Eduardo Coral Chamorro', 'telefono' => '3037890145', 'direccion' => 'Centro, Ipiales, Nariño'],
            ['cedula_nit' => '1085274105', 'nombre' => 'Laura Marcela Benavides Rosero', 'telefono' => '3048901256', 'direccion' => 'Barrio Puenes, Ipiales, Nariño'],
            ['cedula_nit' => '1085274106', 'nombre' => 'Andrés Felipe Enríquez Cabrera', 'telefono' => '3059012367', 'direccion' => 'Barrio El Charco, Ipiales, Nariño'],
            ['cedula_nit' => '1085274107', 'nombre' => 'Paola Andrea Narváez Jojoa', 'telefono' => '3060123478', 'direccion' => 'Barrio La Esmeralda, Ipiales, Nariño'],
            ['cedula_nit' => '1085274108', 'nombre' => 'Miguel Ángel Burbano Revelo', 'telefono' => '3071234589', 'direccion' => 'Barrio San Felipe, Ipiales, Nariño'],
            ['cedula_nit' => '1085274109', 'nombre' => 'Sandra Milena Chamorro Rosas', 'telefono' => '3082345690', 'direccion' => 'Barrio El Placer, Ipiales, Nariño'],
            ['cedula_nit' => '1085274110', 'nombre' => 'Wilson Javier Cárdenas Arteaga', 'telefono' => '3093456701', 'direccion' => 'Barrio Las Cruces, Ipiales, Nariño'],
            ['cedula_nit' => '1085274111', 'nombre' => 'Natalia Alejandra Muñoz Pantoja', 'telefono' => '3104567802', 'direccion' => 'Centro, Pupiales, Nariño'],
            ['cedula_nit' => '1085274112', 'nombre' => 'Diego Fernando Rosero Coral', 'telefono' => '3115678913', 'direccion' => 'Centro, Potosí, Nariño'],
            ['cedula_nit' => '1085274113', 'nombre' => 'Claudia Patricia Guerrero Mora', 'telefono' => '3126789024', 'direccion' => 'Centro, Aldana, Nariño'],
            ['cedula_nit' => '1085274114', 'nombre' => 'Óscar Mauricio Revelo Enríquez', 'telefono' => '3137890135', 'direccion' => 'Centro, Cumbal, Nariño'],
            ['cedula_nit' => '1085274115', 'nombre' => 'Luz Adriana Arteaga Benavides', 'telefono' => '3148901246', 'direccion' => 'Centro, Carlosama, Nariño'],
        ];

        $clientIds = [];

        foreach ($clients as $attributes) {
            $client = Cliente::query()->firstOrCreate(
                ['cedula_nit' => $attributes['cedula_nit']],
                $attributes,
            );

            $clientIds[$attributes['cedula_nit']] = $client->id;
        }

        return $clientIds;
    }

    /**
     * @param  array<string, int>  $categories
     * @param  array<string, int>  $suppliers
     * @param  array<int, array{client: int, method: string, items: array<int, array{sku: string, quantity: int}>}>  $salesPlans
     * @return array<string, Producto>
     */
    private function seedProducts(array $categories, array $suppliers, array $salesPlans): array
    {
        $products = $this->productCatalog();
        $unitsSoldBySku = [];

        foreach ($salesPlans as $salePlan) {
            foreach ($salePlan['items'] as $item) {
                $unitsSoldBySku[$item['sku']] = ($unitsSoldBySku[$item['sku']] ?? 0)
                    + $item['quantity'];
            }
        }

        $productModels = [];

        foreach ($products as $product) {
            $supplierNit = $this->supplierNitForProduct($product['supplier']);
            $initialStock = $product['stock'] + ($unitsSoldBySku[$product['sku']] ?? 0);

            $model = Producto::query()->firstOrCreate(
                ['codigo_sku' => $product['sku']],
                [
                    'nombre' => $product['name'],
                    'categoria_id' => $categories[$product['category']],
                    'proveedor_id' => $suppliers[$supplierNit],
                    'stock' => $initialStock,
                    'precio_compra' => $product['purchase_price'],
                    'precio_venta' => $product['sale_price'],
                ],
            );

            $productModels[$product['sku']] = $model;
        }

        return $productModels;
    }

    /**
     * @param  array<string, int>  $clients
     * @param  array<string, Producto>  $products
     * @param  array<int, array{client: int, method: string, items: array<int, array{sku: string, quantity: int}>}>  $salesPlans
     */
    private function seedSales(User $user, array $clients, array $products, array $salesPlans): void
    {
        $preparedSales = [];

        foreach ($salesPlans as $index => $salePlan) {
            $totalInCents = 0;
            $items = [];

            foreach ($salePlan['items'] as $item) {
                $product = $products[$item['sku']];
                $unitPriceInCents = (int) round((float) $product->precio_venta * 100);
                $lineTotalInCents = $unitPriceInCents * $item['quantity'];
                $totalInCents += $lineTotalInCents;
                $items[] = [
                    'product_id' => $product->id,
                    'sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'unit_price' => number_format($unitPriceInCents / 100, 2, '.', ''),
                    'subtotal' => number_format($lineTotalInCents / 100, 2, '.', ''),
                ];
            }

            $clientDocument = $this->clientDocumentForIndex($salePlan['client']);
            $saleAttributes = [
                'cliente_id' => $clients[$clientDocument],
                'user_id' => $user->id,
                'metodo_pago' => $salePlan['method'],
                'total' => number_format($totalInCents / 100, 2, '.', ''),
                'estado' => 'completada',
            ];

            $preparedSales[] = [
                'identity' => $saleAttributes,
                'items' => $items,
                'created_at' => now()
                    ->subDays($index)
                    ->setTime(9 + ($index % 9), ($index * 7) % 60),
            ];
        }

        $pendingUnitsByProduct = [];

        foreach ($preparedSales as $sale) {
            if (Venta::query()->where($sale['identity'])->exists()) {
                continue;
            }

            foreach ($sale['items'] as $item) {
                $pendingUnitsByProduct[$item['product_id']] =
                    ($pendingUnitsByProduct[$item['product_id']] ?? 0) + $item['quantity'];
            }
        }

        foreach ($products as $product) {
            $pendingUnits = $pendingUnitsByProduct[$product->id] ?? 0;

            if ($product->stock < $pendingUnits) {
                throw new RuntimeException(
                    "No hay stock suficiente para completar las ventas de ejemplo del producto {$product->codigo_sku}. No se modificaron las ventas."
                );
            }
        }

        foreach ($preparedSales as $salePlan) {
            DB::transaction(function () use ($user, $salePlan): void {
                User::query()
                    ->whereKey($user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $sale = Venta::query()->firstOrCreate(
                    $salePlan['identity'],
                    [
                        'created_at' => $salePlan['created_at'],
                        'updated_at' => $salePlan['created_at'],
                    ],
                );

                if (! $sale->wasRecentlyCreated) {
                    return;
                }

                $productIds = collect($salePlan['items'])
                    ->pluck('product_id')
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();

                $lockedProducts = Producto::query()
                    ->whereKey($productIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($salePlan['items'] as $item) {
                    /** @var Producto|null $product */
                    $product = $lockedProducts->get($item['product_id']);

                    if ($product === null || $product->stock < $item['quantity']) {
                        throw new RuntimeException(
                            "El stock del producto {$item['sku']} cambió mientras se registraban las ventas de ejemplo."
                        );
                    }

                    $detail = DetalleVenta::query()->firstOrCreate([
                        'venta_id' => $sale->id,
                        'producto_id' => $product->id,
                        'cantidad' => $item['quantity'],
                        'precio_unitario' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    if ($detail->wasRecentlyCreated) {
                        $product->decrement('stock', $item['quantity']);
                    }
                }
            });
        }
    }

    /**
     * @return array<int, array{sku: string, name: string, category: string, supplier: int, stock: int, purchase_price: int, sale_price: int}>
     */
    private function productCatalog(): array
    {
        return [
            ['sku' => 'FEC-HM-001', 'name' => 'Martillo de uña de acero forjado 16 oz', 'category' => 'Herramientas manuales', 'supplier' => 0, 'stock' => 72, 'purchase_price' => 22000, 'sale_price' => 28500],
            ['sku' => 'FEC-HM-002', 'name' => 'Alicate universal aislado 8 pulgadas', 'category' => 'Herramientas manuales', 'supplier' => 0, 'stock' => 84, 'purchase_price' => 18000, 'sale_price' => 24000],
            ['sku' => 'FEC-HM-003', 'name' => 'Juego de destornilladores 6 piezas', 'category' => 'Herramientas manuales', 'supplier' => 1, 'stock' => 68, 'purchase_price' => 30000, 'sale_price' => 39000],
            ['sku' => 'FEC-HM-004', 'name' => 'Llave ajustable cromada 10 pulgadas', 'category' => 'Herramientas manuales', 'supplier' => 0, 'stock' => 55, 'purchase_price' => 26000, 'sale_price' => 35000],
            ['sku' => 'FEC-HM-005', 'name' => 'Flexómetro resistente de 5 metros', 'category' => 'Herramientas manuales', 'supplier' => 1, 'stock' => 93, 'purchase_price' => 15000, 'sale_price' => 19500],

            ['sku' => 'FEC-HE-001', 'name' => 'Taladro percutor 650 W con mandril', 'category' => 'Herramientas eléctricas', 'supplier' => 1, 'stock' => 62, 'purchase_price' => 145000, 'sale_price' => 188500],
            ['sku' => 'FEC-HE-002', 'name' => 'Pulidora angular 4 1/2 pulgadas 750 W', 'category' => 'Herramientas eléctricas', 'supplier' => 1, 'stock' => 48, 'purchase_price' => 165000, 'sale_price' => 214500],
            ['sku' => 'FEC-HE-003', 'name' => 'Sierra circular eléctrica 7 1/4 pulgadas', 'category' => 'Herramientas eléctricas', 'supplier' => 0, 'stock' => 36, 'purchase_price' => 250000, 'sale_price' => 325000],
            ['sku' => 'FEC-HE-004', 'name' => 'Extensión eléctrica encauchetada 10 metros', 'category' => 'Herramientas eléctricas', 'supplier' => 4, 'stock' => 78, 'purchase_price' => 42000, 'sale_price' => 55000],
            ['sku' => 'FEC-HE-005', 'name' => 'Lijadora orbital eléctrica 240 W', 'category' => 'Herramientas eléctricas', 'supplier' => 1, 'stock' => 45, 'purchase_price' => 110000, 'sale_price' => 143000],

            ['sku' => 'FEC-PA-001', 'name' => 'Pintura vinilo tipo 1 blanca galón', 'category' => 'Pinturas y acabados', 'supplier' => 2, 'stock' => 81, 'purchase_price' => 62000, 'sale_price' => 80000],
            ['sku' => 'FEC-PA-002', 'name' => 'Esmalte sintético negro cuarto', 'category' => 'Pinturas y acabados', 'supplier' => 2, 'stock' => 75, 'purchase_price' => 18000, 'sale_price' => 24000],
            ['sku' => 'FEC-PA-003', 'name' => 'Impermeabilizante acrílico blanco galón', 'category' => 'Pinturas y acabados', 'supplier' => 2, 'stock' => 52, 'purchase_price' => 28000, 'sale_price' => 37000],
            ['sku' => 'FEC-PA-004', 'name' => 'Brocha profesional de 3 pulgadas', 'category' => 'Pinturas y acabados', 'supplier' => 2, 'stock' => 96, 'purchase_price' => 15500, 'sale_price' => 20500],
            ['sku' => 'FEC-PA-005', 'name' => 'Rodillo para pintura con felpa de 9 pulgadas', 'category' => 'Pinturas y acabados', 'supplier' => 2, 'stock' => 67, 'purchase_price' => 33000, 'sale_price' => 43000],

            ['sku' => 'FEC-PL-001', 'name' => 'Tubo PVC presión de 1/2 pulgada x 6 metros', 'category' => 'Plomería', 'supplier' => 3, 'stock' => 88, 'purchase_price' => 22000, 'sale_price' => 29000],
            ['sku' => 'FEC-PL-002', 'name' => 'Grifería sencilla para lavamanos', 'category' => 'Plomería', 'supplier' => 3, 'stock' => 43, 'purchase_price' => 14000, 'sale_price' => 18500],
            ['sku' => 'FEC-PL-003', 'name' => 'Codo PVC presión de 1/2 pulgada', 'category' => 'Plomería', 'supplier' => 3, 'stock' => 118, 'purchase_price' => 9000, 'sale_price' => 12000],
            ['sku' => 'FEC-PL-004', 'name' => 'Válvula de bola PVC de 1/2 pulgada', 'category' => 'Plomería', 'supplier' => 3, 'stock' => 58, 'purchase_price' => 38000, 'sale_price' => 50000],
            ['sku' => 'FEC-PL-005', 'name' => 'Sifón flexible para lavaplatos', 'category' => 'Plomería', 'supplier' => 3, 'stock' => 69, 'purchase_price' => 26000, 'sale_price' => 34000],

            ['sku' => 'FEC-EL-001', 'name' => 'Interruptor sencillo blanco 10 A', 'category' => 'Electricidad', 'supplier' => 4, 'stock' => 105, 'purchase_price' => 6500, 'sale_price' => 8500],
            ['sku' => 'FEC-EL-002', 'name' => 'Cable eléctrico de cobre calibre 12 por metro', 'category' => 'Electricidad', 'supplier' => 4, 'stock' => 93, 'purchase_price' => 25000, 'sale_price' => 33000],
            ['sku' => 'FEC-EL-003', 'name' => 'Tomacorriente doble con polo a tierra', 'category' => 'Electricidad', 'supplier' => 4, 'stock' => 87, 'purchase_price' => 14500, 'sale_price' => 19000],
            ['sku' => 'FEC-EL-004', 'name' => 'Reflector LED exterior 50 W', 'category' => 'Electricidad', 'supplier' => 4, 'stock' => 54, 'purchase_price' => 48000, 'sale_price' => 63000],
            ['sku' => 'FEC-EL-005', 'name' => 'Bombillo LED luz blanca 12 W', 'category' => 'Electricidad', 'supplier' => 4, 'stock' => 120, 'purchase_price' => 18000, 'sale_price' => 24000],

            ['sku' => 'FEC-TF-001', 'name' => 'Tornillo para madera 2 pulgadas paquete', 'category' => 'Tornillería y fijación', 'supplier' => 5, 'stock' => 90, 'purchase_price' => 8500, 'sale_price' => 11000],
            ['sku' => 'FEC-TF-002', 'name' => 'Chazo plástico con tornillo 1/4 paquete', 'category' => 'Tornillería y fijación', 'supplier' => 5, 'stock' => 74, 'purchase_price' => 12000, 'sale_price' => 16000],
            ['sku' => 'FEC-TF-003', 'name' => 'Perno galvanizado 3/8 x 3 pulgadas', 'category' => 'Tornillería y fijación', 'supplier' => 5, 'stock' => 63, 'purchase_price' => 16000, 'sale_price' => 21000],
            ['sku' => 'FEC-TF-004', 'name' => 'Clavo de acero de 2 pulgadas por libra', 'category' => 'Tornillería y fijación', 'supplier' => 5, 'stock' => 82, 'purchase_price' => 6000, 'sale_price' => 8000],
            ['sku' => 'FEC-TF-005', 'name' => 'Anclaje metálico expansivo de 3/8', 'category' => 'Tornillería y fijación', 'supplier' => 5, 'stock' => 59, 'purchase_price' => 22500, 'sale_price' => 29500],

            ['sku' => 'FEC-CC-001', 'name' => 'Bulto de cemento gris uso general 50 kg', 'category' => 'Cemento y construcción', 'supplier' => 6, 'stock' => 110, 'purchase_price' => 25000, 'sale_price' => 32000],
            ['sku' => 'FEC-CC-002', 'name' => 'Bulto de mortero seco para pega 25 kg', 'category' => 'Cemento y construcción', 'supplier' => 6, 'stock' => 71, 'purchase_price' => 16000, 'sale_price' => 21000],
            ['sku' => 'FEC-CC-003', 'name' => 'Malla electrosoldada para placa 2 x 3 metros', 'category' => 'Cemento y construcción', 'supplier' => 6, 'stock' => 32, 'purchase_price' => 52000, 'sale_price' => 68000],
            ['sku' => 'FEC-CC-004', 'name' => 'Ladrillo común de arcilla unidad', 'category' => 'Cemento y construcción', 'supplier' => 6, 'stock' => 145, 'purchase_price' => 8000, 'sale_price' => 10500],
            ['sku' => 'FEC-CC-005', 'name' => 'Arena lavada para construcción bulto', 'category' => 'Cemento y construcción', 'supplier' => 6, 'stock' => 100, 'purchase_price' => 18000, 'sale_price' => 24000],

            ['sku' => 'FEC-SI-001', 'name' => 'Guantes de carnaza para trabajo pesado par', 'category' => 'Seguridad industrial', 'supplier' => 7, 'stock' => 66, 'purchase_price' => 38000, 'sale_price' => 50000],
            ['sku' => 'FEC-SI-002', 'name' => 'Gafas transparentes de seguridad', 'category' => 'Seguridad industrial', 'supplier' => 7, 'stock' => 80, 'purchase_price' => 22000, 'sale_price' => 29000],
            ['sku' => 'FEC-SI-003', 'name' => 'Tapabocas industrial desechable unidad', 'category' => 'Seguridad industrial', 'supplier' => 7, 'stock' => 150, 'purchase_price' => 7000, 'sale_price' => 9500],
            ['sku' => 'FEC-SI-004', 'name' => 'Casco de seguridad con barbuquejo', 'category' => 'Seguridad industrial', 'supplier' => 7, 'stock' => 44, 'purchase_price' => 29000, 'sale_price' => 38000],
            ['sku' => 'FEC-SI-005', 'name' => 'Botas de seguridad punta reforzada par', 'category' => 'Seguridad industrial', 'supplier' => 7, 'stock' => 38, 'purchase_price' => 52000, 'sale_price' => 68000],
        ];
    }

    private function supplierNitForProduct(int $supplierIndex): string
    {
        return [
            '901456780-1',
            '901456781-9',
            '901456782-7',
            '901456783-5',
            '901456784-3',
            '901456785-1',
            '901456786-0',
            '901456787-8',
        ][$supplierIndex];
    }

    private function clientDocumentForIndex(int $clientIndex): string
    {
        return [
            '1085274101',
            '1085274102',
            '1085274103',
            '1085274104',
            '1085274105',
            '1085274106',
            '1085274107',
            '1085274108',
            '1085274109',
            '1085274110',
            '1085274111',
            '1085274112',
            '1085274113',
            '1085274114',
            '1085274115',
        ][$clientIndex];
    }

    /**
     * @return array<int, array{client: int, method: string, items: array<int, array{sku: string, quantity: int}>}>
     */
    private function salesPlans(): array
    {
        $methods = ['Efectivo', 'Tarjeta', 'Transferencia'];

        return [
            ['client' => 0, 'method' => $methods[0], 'items' => [['sku' => 'FEC-HM-001', 'quantity' => 1], ['sku' => 'FEC-HM-002', 'quantity' => 2]]],
            ['client' => 1, 'method' => $methods[1], 'items' => [['sku' => 'FEC-HM-003', 'quantity' => 1], ['sku' => 'FEC-HE-001', 'quantity' => 1]]],
            ['client' => 2, 'method' => $methods[2], 'items' => [['sku' => 'FEC-HE-002', 'quantity' => 1], ['sku' => 'FEC-PA-001', 'quantity' => 1]]],
            ['client' => 3, 'method' => $methods[0], 'items' => [['sku' => 'FEC-PA-002', 'quantity' => 2], ['sku' => 'FEC-PL-001', 'quantity' => 1]]],
            ['client' => 4, 'method' => $methods[1], 'items' => [['sku' => 'FEC-PL-002', 'quantity' => 2], ['sku' => 'FEC-EL-001', 'quantity' => 3]]],
            ['client' => 5, 'method' => $methods[2], 'items' => [['sku' => 'FEC-EL-002', 'quantity' => 1], ['sku' => 'FEC-TF-001', 'quantity' => 2]]],
            ['client' => 6, 'method' => $methods[0], 'items' => [['sku' => 'FEC-TF-002', 'quantity' => 3], ['sku' => 'FEC-CC-001', 'quantity' => 2]]],
            ['client' => 7, 'method' => $methods[1], 'items' => [['sku' => 'FEC-CC-002', 'quantity' => 4], ['sku' => 'FEC-SI-001', 'quantity' => 1]]],
            ['client' => 8, 'method' => $methods[2], 'items' => [['sku' => 'FEC-SI-002', 'quantity' => 2], ['sku' => 'FEC-HM-004', 'quantity' => 1]]],
            ['client' => 9, 'method' => $methods[0], 'items' => [['sku' => 'FEC-HM-005', 'quantity' => 2], ['sku' => 'FEC-HE-003', 'quantity' => 1]]],
            ['client' => 10, 'method' => $methods[1], 'items' => [['sku' => 'FEC-HE-004', 'quantity' => 1], ['sku' => 'FEC-PA-003', 'quantity' => 2]]],
            ['client' => 11, 'method' => $methods[2], 'items' => [['sku' => 'FEC-PA-004', 'quantity' => 2], ['sku' => 'FEC-PL-003', 'quantity' => 4]]],
            ['client' => 12, 'method' => $methods[0], 'items' => [['sku' => 'FEC-PL-004', 'quantity' => 1], ['sku' => 'FEC-EL-003', 'quantity' => 3]]],
            ['client' => 13, 'method' => $methods[1], 'items' => [['sku' => 'FEC-EL-004', 'quantity' => 1], ['sku' => 'FEC-TF-003', 'quantity' => 2]]],
            ['client' => 14, 'method' => $methods[2], 'items' => [['sku' => 'FEC-TF-004', 'quantity' => 3], ['sku' => 'FEC-CC-003', 'quantity' => 1]]],
            ['client' => 0, 'method' => $methods[1], 'items' => [['sku' => 'FEC-CC-004', 'quantity' => 4], ['sku' => 'FEC-SI-003', 'quantity' => 2]]],
            ['client' => 1, 'method' => $methods[2], 'items' => [['sku' => 'FEC-SI-004', 'quantity' => 1], ['sku' => 'FEC-HM-001', 'quantity' => 1]]],
            ['client' => 2, 'method' => $methods[0], 'items' => [['sku' => 'FEC-HE-005', 'quantity' => 1], ['sku' => 'FEC-PA-005', 'quantity' => 2]]],
            ['client' => 3, 'method' => $methods[1], 'items' => [['sku' => 'FEC-PL-005', 'quantity' => 2], ['sku' => 'FEC-EL-005', 'quantity' => 2]]],
            ['client' => 4, 'method' => $methods[2], 'items' => [['sku' => 'FEC-TF-005', 'quantity' => 1], ['sku' => 'FEC-CC-005', 'quantity' => 2], ['sku' => 'FEC-SI-005', 'quantity' => 1]]],
        ];
    }
}
