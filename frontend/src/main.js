/***
 * Importalçoes principais do projeto
 * 
 * -createApp: função do Vue 3 usada para cirar uma nova instancai da aplicação
 * - App.vue: componente raiz da aplicação (o ponto incial da interface).
 *  - router: responsvel pelas rotas do sistema (navegação entre telas)
 *  - axios: biblioteca para fazer requisilçies HTTP para o backend Laravavel
 *  - VueTheMask: plugin usado para aplicar mascata (CPF, Moeda, etc)
 */

import { createApp } from 'vue'
import App from "./App.vue"
import route from "./router"
import axios from 'axios'
import VueTheMask from 'vue-the-mask'



/**
 * Criacao da aplicação Vue
 * 
 * aqui é onde a aplicação Vue é criada com base no componente principal;
 * essa instancia e usada para configurar plugnis, variaveis globais e montar o app no DOM.
 */
const app = createApp(App)


/**
 * Configuração global do Axios
 * 
 * Define a URL base usada em todas requisições HTTP
 * 
 * Assim, ao usar 'axios.get('/propostas')', o vue automaticamente acessara:
 * http://127.0.0.1:8000/api/propostas
 */

axios.defaults.baseURL = 'http://127.0.0.1:8000/api'

/**
 * Integracao global do axios com o vue
 * 
 * isso permite acessar o axios em qualquer componente usando:
 * this.$axios (no setup script, tambem pode ser importado diretamente)
 * 
 */
app.config.globalProperties.$axios = axios



/** registra o sistema de rotas
 * 
 * o vue Router controla as paginas da aplicacao (Ex: /propostas /nova_proposta)
 */
app.use(route)

/**
 * Registra o plugin VueTheMask
 * 
 * Usado para aplicar máscatas de entrada em campos;
 */
app.use(VueTheMask)


/**
 * Monta a aplicaçãpo no DOm
 * 
 * app.mount('#app') liga a instancia Vue ao elemento HTML com id='app' (geralmente no index.html)
 * A partir desse momento, o Vue controla toda a interface da aplicação
 */
app.mount('#app')