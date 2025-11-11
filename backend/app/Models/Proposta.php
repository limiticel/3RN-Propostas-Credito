use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model

//** 
*Classe Proposta
*
 *Representa o modelo de uma proposta de crédito no banco de dados.
 * @package App\Models
 *
 * @property int $id
 * @property string $nome_cliente
 * @propery string $cpf
 * @property float $valor_solicitado
 * @property int $quantidade_parcelas
 * @property float $valor_parcela
 * @property float $valor_total
 * @property float $salario
 * @property float $taxa_juros
 * @property float $margem_disponivel
 * @property string|null $observacoes
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Proposta whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposta whereCpf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Proposta whereNomeCliente($value)

*/
class Proposta extends Model
{
    use HasFactory; 
    /** 
    * Nome da tabela associada a este modelo.
    *
    * @var string
    */
    protected $table = 'propostas';

    /**
    *campos que podem ser atribuidos em massa.
    *
    * @var array<int,string>
    */

    protected $fillable = [
        'nome_cliente',
        'cpf',
        'valor_solicitado',
        'quantidade_parcelas',
        'valor_parcela',
        'valor_total',
        'salario',
        'taxa_juros',
        'margem_disponivel',
        'observacoes',
        'status'

        ];

        /**
        *Conversões automatica de tipo para atributos.
        *
        * @var array<string, string>
        */
        protected $casts = [
            'valor_solicitado' => 'float',
            'valor_parcela' => 'float',
            'valor_total' => 'float'
            
            ];
}


