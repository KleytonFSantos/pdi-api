## Objetivo: Pagamento Simplificado

Temos 2 tipos de usuários, os comuns e lojistas, ambos têm carteira com dinheiro e realizam transferências entre eles. Vamos nos atentar **somente** ao fluxo de transferência entre dois usuários.

Requisitos:

-   Para ambos tipos de usuário, precisamos do Nome Completo, CPF, e-mail e Senha. CPF/CNPJ e e-mails devem ser únicos no sistema. Sendo assim, seu sistema deve permitir apenas um cadastro com o mesmo CPF ou endereço de e-mail.

-   Usuários podem enviar dinheiro (efetuar transferência) para lojistas e entre usuários.

-   Lojistas **só recebem** transferências, não enviam dinheiro para ninguém.

-   Validar se o usuário tem saldo antes da transferência.

-   Antes de finalizar a transferência, deve-se consultar um serviço autorizador externo, use este mock para simular ([https://run.mocky.io/v3/a44f11a6-1788-4160-bc48-610e66f8386b](https://run.mocky.io/v3/a44f11a6-1788-4160-bc48-610e66f8386b)).

-   A operação de transferência deve ser uma transação (ou seja, revertida em qualquer caso de inconsistência) e o dinheiro deve voltar para a carteira do usuário que envia.

-   No recebimento de pagamento, o usuário ou lojista precisa receber notificação (envio de email, sms) enviada por um serviço de terceiro e eventualmente este serviço pode estar indisponível/instável. Use este mock para simular o envio ([https://run.mocky.io/v3/54dc2cf1-3add-45b5-b5a9-6bf7e7f1f4a6](https://run.mocky.io/v3/718a34d0-7c3e-4ade-904b-6319b778be62)).

-   Este serviço deve ser RESTFul.

### Payload

POST /transaction

```json
{
    "value": 100.0,
    "payer": 4,
    "payee": 15
}
```
