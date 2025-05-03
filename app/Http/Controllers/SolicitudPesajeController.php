
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SolicitudPesaje;

class SolicitudPesajeController extends Controller
{
public function store(Request $request)
{
$request->validate([
'cantidad_total' => 'required|numeric|min:0',
'medida_peso_id' => 'required|exists:medidas_peso,id',
'tolerancia' => 'required|numeric|min:0|max:100',
'precio_unitario' => 'required|numeric|min:0',
'cantidad_parcialidades' => 'required|integer|min:1'
]);

$solicitud = SolicitudPesaje::create([
'cantidad_total' => $request->cantidad_total,
'medida_peso_id' => $request->medida_peso_id,
'tolerancia' => $request->tolerancia,
'precio_unitario' => $request->precio_unitario,
'cantidad_parcialidades' => $request->cantidad_parcialidades,
'estado_id' => 1, // Estado INICIAL o PENDIENTE
'agricultor_id' => Auth::user()->agricultor->id
]);

return response()->json([
'message' => 'Solicitud enviada correctamente',
'solicitud' => $solicitud
], 201);
}
}
