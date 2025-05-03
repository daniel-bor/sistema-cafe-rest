namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Parcialidad;
use App\Models\SolicitudPesaje;

class ParcialidadController extends Controller
{
public function store(Request $request)
{
$request->validate([
'pesaje_id' => 'required|exists:pesajes,id',
'transporte_id' => 'required|exists:transportes,id',
'transportista_id' => 'required|exists:transportistas,id',
'peso' => 'required|numeric|min:0',
'tipo_medida' => 'required|string',
'fecha_recepcion' => 'required|date',
'estado_id' => 'required|exists:estados,id',
]);

// Verifica si el agricultor actual es dueño del pesaje
$user = Auth::user();
$agricultor = $user->agricultor;

$pesaje = \App\Models\Pesaje::findOrFail($request->pesaje_id);
if ($pesaje->cuenta->agricultor_id !== $agricultor->id) {
return response()->json(['error' => 'No autorizado para este pesaje'], 403);
}

$parcialidad = Parcialidad::create([
'pesaje_id' => $request->pesaje_id,
'transporte_id' => $request->transporte_id,
'transportista_id' => $request->transportista_id,
'peso' => $request->peso,
'tipo_medida' => $request->tipo_medida,
'fecha_recepcion' => $request->fecha_recepcion,
'estado_id' => $request->estado_id,
'codigo_qr' => uniqid('QR'), // Simulación QR único
]);

return response()->json([
'message' => 'Parcialidad registrada',
'parcialidad' => $parcialidad
], 201);
}
}
