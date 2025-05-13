namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesData;

class SalesDataController extends Controller
{
    public function index()
    {
        $data = SalesData::where('store', 1)->where('department', 1)->orderBy('date')->get();

        $labels = $data->pluck('date');
        $values = $data->pluck('sales');

        return view('sales_graph', compact('labels', 'values'));
    }
}
