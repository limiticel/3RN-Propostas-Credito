<template>
  <div class="container">
    <h1>Cadastro de Proposta</h1>

    <form @submit.prevent="enviarProposta" class="form">
      <!-- Nome -->
      <label>Nome completo</label>
      <input
        type="text"
        v-model="proposta.nome_cliente"
        :class="{ erro: erros.nome_cliente }"
        placeholder="Digite o nome"
        required
      />

      <!-- CPF -->
      <label>CPF</label>
      <input
        type="text"
        v-mask="'###.###.###-##'"
        v-model="proposta.cpf"
        :class="{ erro: erros.cpf }"
        placeholder="000.000.000-00"
        required
      />

      <!-- Salário -->
      <label>Salário</label>
      <input
        type="text"
        v-model="proposta.salario"
        @input="calcularValores"
        placeholder="R$ 0.000,00"
        :class="{ erro: erros.salario }"
        required
      />

      <!-- Valor solicitado -->
      <label>Valor Solicitado</label>
      <input
        type="text"
        v-model="proposta.valor_solicitado"
        @input="calcularValores"
        placeholder="R$ 0.000,00"
        :class="{ erro: erros.valor_solicitado }"
        required
      />

      <!-- Prazo -->
      <label>Prazo (meses)</label>
      <select
        v-model="proposta.quantidade_parcelas"
        @change="calcularValores"
        required
      >
        <option disabled value="">Selecione</option>
        <option v-for="mes in prazos" :key="mes" :value="mes">{{ mes }}</option>
      </select>

      <!-- Observações -->
      <label>Observações</label>
      <textarea
        v-model="proposta.observacoes"
        placeholder="Opcional"
      ></textarea>

      <!-- Cálculos em tempo real -->
      <div class="resultado" v-if="valores.margemDisponivel">
        <p><strong>Margem disponível:</strong> R$ {{ valores.margemDisponivel }}</p>
        <p><strong>Valor da parcela estimado:</strong> R$ {{ valores.valorParcela }}</p>
        <p><strong>Valor total:</strong> R$ {{ valores.valorTotal }}</p>
      </div>

      <!-- Botão -->
      <button type="submit" :disabled="carregando">
        {{ carregando ? 'Enviando...' : 'Enviar Proposta' }}
      </button>
      <button type="button" @click="irParaListagem" class="btn-listar">
  Ver Propostas
</button>
    </form>

    <div v-if="mensagem" class="mensagem">{{ mensagem }}</div>
  </div>
</template>

<script setup>
// Palavras chave: Js, Javascript, vue.js
// Importações principais do Vue e bibliotecas usadas
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'


// Instancia o roteador para redirecionar páginas 
const router = useRouter()


/**
 * Redireciona o usuario para a rota de listagem de proposta
 */

const irParaListagem = () =>{
  router.push('/propostas')
}

/**
 * Objeto reativo com os campos do formulario da proposta
 * ref() é usado para manter e reatividade (ligado ao template com v-model)
 */
const proposta = ref({
  nome_cliente: '',
  cpf: '',
  salario: '',
  valor_solicitado: '',
  quantidade_parcelas: '',
  observacoes: ''
})

//opções fixas de prazos
const prazos = [6, 12, 18, 24, 36, 48, 60]

//mensagem de feedback para o usuario
//sucesso, erro ou validação
const mensagem = ref('')

//Indicador de carregamento
const carregando = ref(false)


/**
 * Objeto para armazenar erros de validação retornados pelo backend
 * exemplo: {cpd: ["O CPF é obrigatório"]}
 */

const erros = ref({})

/**
 * Armazena valores calculados da proposta
 */
const valores = ref({
  margemDisponivel: null,
  valorParcela: null,
  valorTotal: null
})


/**
 * formata um numero para padrao em real(R$ 0,00)
 * @param {number} num - Valor numerico
 * @returns {string} Valor formatado
 */
const formatarNumero = (num) => {
  return num ? num.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) : '0,00'
}


/**
 * Calcula valores da proposta (margem, valor da parcela e total)
 * Usa juros compostos de 2,5% ao mes e 30 da renda como limite
 */
const calcularValores = () => {
    //converte os campos de entrada
  const salario = parseFloat(proposta.value.salario.replace(/[R$.\s]/g, '').replace(',', '.')) || 0
  const valor = parseFloat(proposta.value.valor_solicitado.replace(/[R$.\s]/g, '').replace(',', '.')) || 0
  const parcelas = Number(proposta.value.quantidade_parcelas)
    // se algum gampo estiver vazio, limpa os valores calculados
  if (!salario || !valor || !parcelas) {
    valores.value = { margemDisponivel: null, valorParcela: null, valorTotal: null }
    return
  }

  // formulas financeiras
  const margem = salario * 0.3 //30%
  const taxa = 0.025 //2,5 ao mes
  const pow = Math.pow(1 + taxa, parcelas)
  const valorParcela = valor * (taxa * pow) / (pow - 1)
  const valorTotal = valorParcela * parcelas

    //atualização dos valores formatados
  valores.value = {
    margemDisponivel: formatarNumero(margem),
    valorParcela: formatarNumero(valorParcela),
    valorTotal: formatarNumero(valorTotal)
  }
}


/**
 * Envia os dados da proposta para o backend(Laravel API)
 * Faz a requisição POST para '/api/propostas'
 */

const enviarProposta = async () => {
  try {
    carregando.value = true
    erros.value = {}//limpa os erros anteriores

    // Prepara o corpo da requsição (Convertendo strings para números)
    const body = {
      nome_cliente: proposta.value.nome_cliente,
      cpf: proposta.value.cpf,
      salario: parseFloat(proposta.value.salario.replace(/[R$.\s]/g, '').replace(',', '.')),
      valor_solicitado: parseFloat(proposta.value.valor_solicitado.replace(/[R$.\s]/g, '').replace(',', '.')),
      quantidade_parcelas: Number(proposta.value.quantidade_parcelas),
      observacoes: proposta.value.observacoes
    }

    // Envia a proposta para o backend
    const response = await axios.post('/propostas', body)

    //Mostra a mensagem de sucesso com ID retornado pela API
    mensagem.value = `✅ Proposta criada com sucesso! ID: ${response.data.data.id}`

    // limpar formulário
    proposta.value = {
      nome_cliente: '',
      cpf: '',
      salario: '',
      valor_solicitado: '',
      quantidade_parcelas: '',
      observacoes: ''
    }
    valores.value = { margemDisponivel: null, valorParcela: null, valorTotal: null }

  } catch (error) {
    // tratamento de erros de validação
    if (error.response?.data?.errors) {
        erros.value = error.response.data.errors
      const listaErros = Object.values(erros.value).flat().join(' | ')
      mensagem.value = `❌ ${listaErros}`
    } else {
        //erro generico
      mensagem.value = '❌ Erro ao enviar a proposta.'
    }
  } finally {
    // desativa o carregamento final
    carregando.value = false
  }
}
</script>

<style scoped>
.container {
  max-width: 600px;
  margin: 30px auto;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  padding: 25px;
}
h1 {
  text-align: center;
  margin-bottom: 20px;
  color: #333;
}
label {
  font-weight: 600;
  display: block;
  margin-top: 10px;
}
input,
select,
textarea {
  width: 100%;
  padding: 10px;
  margin-top: 5px;
  border-radius: 6px;
  border: 1px solid #ccc;
  transition: 0.2s;
}
input.erro,
select.erro {
  border-color: red;
}
button {
  width: 100%;
  background: #007bff;
  color: white;
  border: none;
  padding: 10px;
  margin-top: 15px;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.3s;
}
button:hover {
  background: #0056b3;
}
.mensagem {
  margin-top: 15px;
  padding: 10px;
  background: #f0f0f0;
  border-radius: 6px;
}
.resultado {
  margin-top: 15px;
  background: #eef9f0;
  padding: 10px;
  border-radius: 6px;
  color: #333;
}
</style>
