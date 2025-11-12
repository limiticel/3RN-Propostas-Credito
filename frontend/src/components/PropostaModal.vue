<template>
  <div class="modal-overlay" v-if="visivel">
    <div class="modal">
      <button class="fechar" @click="fechar">✖</button>
      <h2>Detalhes da Proposta #{{ proposta.id }}</h2>


      <div class="info">
        <p><strong>Cliente:</strong> {{ proposta.nome_cliente }}</p>
        <p><strong>CPF:</strong> {{ proposta.cpf }}</p>
        <p><strong>Salário:</strong> R$ {{ formatar(proposta.salario) }}</p>
        <p><strong>Valor Solicitado:</strong> R$ {{ formatar(proposta.valor_solicitado) }}</p>
        <p><strong>Parcelas:</strong> {{ proposta.quantidade_parcelas }}x</p>
        <p><strong>Valor da Parcela:</strong> R$ {{ formatar(proposta.valor_parcela) }}</p>
        <p><strong>Valor Total:</strong> R$ {{ formatar(proposta.valor_total) }}</p>
        <p>
          <strong>Status:</strong>
          <span :class="['badge', proposta.status]">{{ proposta.status }}</span>
        </p>
        <p v-if="proposta.observacoes"><strong>Observações:</strong> {{ proposta.observacoes }}</p>
      </div>

      <div class="acoes">
        <button class="em-analise" @click="atualizarStatus('em_analise')">🔎 Enviar para Análise</button>
        <button class="aprovar" @click="atualizarStatus('aprovada')">✅ Aprovar</button>
        <button class="reprovar" @click="atualizarStatus('reprovada')">❌ Reprovar</button>
        <button class="cancelar" @click="atualizarStatus('cancelada')">🗑️ Cancelar</button>
      </div>

      <div v-if="mensagem" class="mensagem">{{ mensagem }}</div>
    </div>
  </div>
</template>

<script setup>

// Importações do Vue e da biblioteca axios
// ref -> cria variaveis reativas
// axios -> faz requisições HTTP para o backend (Laravel API)
import { ref, watch } from 'vue'
import axios from 'axios'

/**
 * DefineProps -> recebe propriedades (dados e estados) do componente pai
 * proposta: objeto contendo as informçãoes da proposta selecionada
 * visivel: booleano que indica se o modal deve estar aberto
 */
const props = defineProps({
  proposta: Object,
  visivel: Boolean
})

/**
 * defineEmits -> define os eventos que este componente pode emitir para o pai
 * fechar -> para o pai ocultar o modal
 * atualizou -> para o pai recarrefar a lista de prospostas após uma alteração
 * 
 */
const emit = defineEmits(['fechar', 'atualizou'])

/**
 * mesagem -> variavel reativa usada para exibir feedback ao usuario
 * ex: status alterado com sucesso ou erro ao atualizar status
 * 
 */
const mensagem = ref('')


/**
 * Função fechar()
 * limpa qualquer mensagem exibida
 * Emite o evento 'fechar' para avisar o componente pai que o model deve ser fechado
 */
const fechar = () => {
  mensagem.value = ''
  emit('fechar')
}


/**
 * Funcao formatar
 * converte valores numericos em formato monetario brasileiro
 * usada no template para exibir valores de forma amigavel
 */
const formatar = (valor) => {
  return valor ? valor.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) : '0,00'
}

/**
 * Funcao atualizarStatus(novoStatus)
 * recebe o novo status (Ex: "aprovada","reprovada" ou "cancelada")
 * faz um requisição PATCH para o backend Laravel:
 * /propostas/{id}/status
 * Atualiza a mensagem de sucesso ou erro
 * Emite o evento 'atualizou' para atualizar a lista principal no componente pai
 * fecha o modal automaticamente apos 1.2 segundos
 */
const atualizarStatus = async (novoStatus) => {
  try {
    await axios.patch(`/propostas/${props.proposta.id}/status`, { status: novoStatus })
    // Exibe mensagem de sucesso
    mensagem.value = `✅ Status alterado para "${novoStatus}".`
    
    emit('atualizou') // recarrega lista principal
    // Fecha o modal após 1.2
    setTimeout(() => fechar(), 1200)
  } catch (error) {
    mensagem.value = '❌ Erro ao atualizar status.'
  }
}
</script>

<style scoped>
/**
Model-overlay

cria o fundo semitransparente que cobre a tela toda quando o modal esta aberto
Serve para destacar o modal e impedir interação como restante da pagina.


*/

.modal-overlay {
  position: fixed;                     /**fixa o overlay na tela, mesmo com rolagem */
  top: 0;
  left: 0;
  width: 100%;                       /** cobre a largura da janela */
  height: 100%;                     /**Cobre toda a altura da janela */
  background: rgba(0, 0, 0, 0.6); /** fundo preto translucido */
  display: flex;                    /** Centraliza o modal */            
  justify-content: center;          /** Alinhamento na horizontal */
  align-items: center;              /** alinhamento na vertical */
  z-index: 1000;                    /** Garante que o modal fique sobre os outros elementos */
}

/** Modal
Define a caixa branca central do modal, com borda arrendondadas, sombra leve
*/
.modal {
  background: #fff;
  border-radius: 10px;
  width: 500px;
  padding: 20px;
  position: relative;
  animation: fadeIn 0.3s ease;
}


/**
* Estiliza o botao "X" de fechar no canto superior no modal
*/
.fechar {
  position: absolute;
  top: 10px;
  right: 10px;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 20px;
}

/**
Define o espaçamento entre os paragrafos
*/
.info p {
  margin: 6px 0;
}

/* 
badge
Estiliza os rótulos de status (rascunho, aprovada, reprovada, etc.)
Cada status tem uma cor específica para identificação visual.
*/
.badge {
  padding: 4px 10px;           /* Espaçamento interno */
  border-radius: 8px;          /* Bordas arredondadas */
  color: white;                /* Texto branco */
  text-transform: capitalize;  /* Primeira letra maiúscula */
}


/* Cores específicas para cada status */
.badge.rascunho { background-color: gray; }
.badge.em_analise { background-color: orange; }
.badge.aprovada { background-color: green; }
.badge.reprovada { background-color: red; }
.badge.cancelada { background-color: #555; }


/* 
acoes
Container dos botões (aprovar, reprovar, etc.)
Os botões são distribuídos de forma uniforme.
*/
.acoes {
  display: flex;
  justify-content: space-around;
  margin-top: 15px;            /* Espaço acima dos botões */
}

/* 
acoes button
Estilização base para todos os botões de ação.
Inclui padding, cor do texto, arredondamento e transição suave ao passar o mouse.
*/
.acoes button {
  border: none;
  color: white;
  padding: 10px 15px;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.2s;            /* Efeito suave de hover */
}

/* Cores personalizadas para cada tipo de ação */
.acoes .em-analise { background-color: #555; }   /* Enviar para análise */
.acoes .aprovar { background-color: #28a745; }   /* Verde - Aprovar */
.acoes .reprovar { background-color: #dc3545; }  /* Vermelho - Reprovar */
.acoes .cancelar { background-color: #6c757d; }  /* Cinza - Cancelar */

/* 
mensagem
Área de feedback para mostrar mensagens de sucesso ou erro.
*/
.mensagem {
  margin-top: 10px;
  text-align: center;
  background: #f0f0f0;
  padding: 8px;
  border-radius: 6px;
}

/* 
 @keyframes fadeIn
Animação que faz o modal “surgir” de forma suave.
Aumenta a opacidade e o tamanho levemente.
*/
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }  /* Início: invisível e levemente menor */
  to { opacity: 1; transform: scale(1); }       /* Fim: visível e em tamanho normal */
}
</style>
