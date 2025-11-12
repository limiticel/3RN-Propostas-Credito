<template>
  <div class="container">
    <h1>Listagem de Propostas</h1>

    <!-- Filtros -->
    <div class="filtros">
      <input
        type="text"
        v-model="busca"
        placeholder="Buscar por nome ou CPF..."
        @input="carregarPropostas"
      />

      <select v-model="filtroStatus" @change="carregarPropostas">
        <option value="">Todos os status</option>
        <option v-for="st in statusDisponiveis" :key="st" :value="st">
          {{ st }}
        </option>
      </select>
      <button class="btn-nova" @click="irParaNovaProposta">+ Nova Proposta</button>
    </div>

    <!-- Tabela -->
    <table class="tabela">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>Valor Solicitado</th>
          <th>Prazo</th>
          <th>Parcela</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="p in propostas" :key="p.id">
          <td>
            <strong>{{ p.nome_cliente }}</strong><br />
            <small>{{ mascararCPF(p.cpf) }}</small>
          </td>
          <td>R$ {{ formatar(p.valor_solicitado) }}</td>
          <td>{{ p.quantidade_parcelas }}x</td>
          <td>R$ {{ formatar(p.valor_parcela) }}</td>
          <td>
            <span :class="['badge', p.status]">{{ p.status }}</span>
          </td>
          <td class="acoes">
            <button @click="verDetalhes(p)">👁️</button>
            <button @click="editarStatus(p)">✏️</button>
            <button @click="cancelarProposta(p)">❌</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Paginação -->
    <div class="paginacao">
      <button :disabled="paginaAtual === 1" @click="paginaAtual-- && carregarPropostas()">
        ◀ Anterior
      </button>
      <span>Página {{ paginaAtual }}</span>
      <button :disabled="!temMais" @click="paginaAtual++ && carregarPropostas()">
        Próxima ▶
      </button>
    </div>

    <div v-if="mensagem" class="mensagem">{{ mensagem }}</div>
  </div>
  <PropostaModal
    v-if="propostaSelecionada"
    :visivel="modalVisivel"
    :proposta="propostaSelecionada"
    @fechar="modalVisivel = false"
    @atualizou="carregarPropostas"
  />

</template>

<script setup>

import PropostaModal from "../components/PropostaModal.vue"
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
const router = useRouter()

const irParaNovaProposta = () => {
  router.push("/")
}

const propostas = ref([])
const paginaAtual = ref(1)
const limite = 5
const temMais = ref(false)
const mensagem = ref('')
const busca = ref('')
const filtroStatus = ref('')
const statusDisponiveis = ['rascunho', 'em_analise', 'aprovada', 'reprovada', 'cancelada']

const modalVisivel = ref(false)
const propostaSelecionada = ref(null)


const verDetalhes = (p) => {
  propostaSelecionada.value = p
  modalVisivel.value = true
}

const carregarPropostas = async () => {
  try {
    const params = {
      page: paginaAtual.value,
      per_page: limite,
      busca: busca.value,
      status: filtroStatus.value
    }

    const { data } = await axios.get('/propostas', { params })
    propostas.value = data.data || data
    temMais.value = data.next_page_url !== null
  } catch (error) {
    mensagem.value = '❌ Erro ao carregar as propostas.'
    console.error(error)
  }
}

const mascararCPF = (cpf) => {
  if (!cpf) return ''
  return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
}

const formatar = (valor) =>
  valor ? valor.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) : '0,00'

// const verDetalhes = (p) => {
//   alert(`
// Proposta #${p.id}
// Cliente: ${p.nome_cliente}
// CPF: ${p.cpf}
// Valor solicitado: R$ ${formatar(p.valor_solicitado)}
// Valor parcela: R$ ${formatar(p.valor_parcela)}
// Status: ${p.status}
// `)
// }

const editarStatus = async (p) => {
  const novoStatus = prompt(
    `Digite o novo status para ${p.nome_cliente} (${p.status}):\nrascunho, em_analise, aprovada, reprovada, cancelada`
  )
  if (!novoStatus) return

  try {
    await axios.patch(`/propostas/${p.id}/status`, { status: novoStatus })
    mensagem.value = '✅ Status atualizado!'
    carregarPropostas()
  } catch {
    mensagem.value = '❌ Erro ao atualizar status.'
  }
}

const cancelarProposta = async (p) => {
  if (!confirm(`Cancelar proposta de ${p.nome_cliente}?`)) return
  try {
    await axios.delete(`/propostas/${p.id}`)
    mensagem.value = '🗑️ Proposta cancelada!'
    carregarPropostas()
  } catch {
    mensagem.value = '❌ Erro ao cancelar proposta.'
  }
}

onMounted(carregarPropostas)
watch([busca, filtroStatus], () => {
  paginaAtual.value = 1
})
</script>

<style scoped>
/* 
.container
Cria a caixa principal da página de listagem.
Centraliza o conteúdo, aplica fundo branco e sombra leve.
*/
.container {
  max-width: 900px;                     /* Limita a largura máxima */
  margin: 30px auto;                    /* Centraliza na horizontal com margem vertical */
  background: #fff;                     /* Fundo branco */
  padding: 25px;                        /* Espaçamento interno */
  border-radius: 12px;                  /* Bordas arredondadas */
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Sombra suave ao redor */
}

/* 
h1
Título da página (ex: “Listagem de Propostas”)
Centraliza o texto e adiciona espaçamento inferior.
*/
h1 {
  text-align: center;
  margin-bottom: 20px;
}

/* 
filtros
Área que contém os campos de busca e filtro de status.
Usa flexbox para distribuir os elementos lado a lado.
*/
.filtros {
  display: flex;                        /* Coloca os elementos na mesma linha */
  justify-content: space-between;       /* Espaço uniforme entre os filtros */
  gap: 10px;                            /* Espaçamento entre os campos */
  margin-bottom: 15px;                  /* Espaço entre filtros e tabela */
}

/* 
filtros input, .filtros select
Estilo dos campos de busca e do seletor de status.
Mantêm aparência limpa e moderna.
*/
.filtros input, .filtros select {
  padding: 8px;                         /* Espaçamento interno */
  width: 48%;                           /* Ocupa metade da linha cada */
  border-radius: 6px;                   /* Bordas levemente arredondadas */
  border: 1px solid #ccc;               /* Borda cinza clara */
}

/* 
tabela
Define o estilo da tabela de listagem de propostas.
*/
.tabela {
  width: 100%;                          /* Ocupa toda a largura do container */
  border-collapse: collapse;            /* Remove espaçamentos entre bordas */
  margin-top: 10px;                     /* Espaço entre a tabela e os filtros */
}

/* 
tabela th, .tabela td
Define estilo das células da tabela — cabeçalhos e dados.
*/
.tabela th, .tabela td {
  border: 1px solid #eee;               /* Borda leve entre células */
  padding: 10px;                        /* Espaçamento interno */
  text-align: left;                     /* Alinhamento do texto à esquerda */
}

/* 
badge
Rótulos coloridos que indicam o status da proposta (igual aos do modal).
*/
.badge {
  padding: 5px 10px;
  border-radius: 8px;
  color: white;
  text-transform: capitalize;
}

/*  Cores dos status */
.badge.rascunho { background-color: gray; }
.badge.em_analise { background-color: orange; }
.badge.aprovada { background-color: green; }
.badge.reprovada { background-color: red; }
.badge.cancelada { background-color: #555; }

/* 
acoes button
Botões de ação dentro da tabela (ver detalhes, editar, excluir).
Usa ícones ao invés de botões grandes.
*/
.acoes button {
  margin-right: 5px;                    /* Espaço entre botões */
  cursor: pointer;                      /* Mostra o cursor de clique */
  border: none;                         /* Remove borda padrão */
  background: none;                     /* Fundo transparente */
  font-size: 18px;                      /* Ícones maiores */
}

/* 
paginacao
Centraliza os botões de navegação entre páginas.
*/
.paginacao {
  margin-top: 15px;
  text-align: center;
}

/* 
mensagem
Área para exibir mensagens de sucesso, erro ou feedbacks gerais.
*/
.mensagem {
  margin-top: 10px;
  padding: 8px;
  background: #f0f0f0;
  border-radius: 6px;
  text-align: center;
}

</style>
