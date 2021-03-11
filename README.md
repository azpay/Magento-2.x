
![Logo](https://www.azpay.com.br/blog/wp-content/uploads/2018/11/Screen-Shot-2018-11-25-at-13.17.28-768x429.png)

# Módulo de pagamento para Magento 2

Testado na versão 2.3.1 e superior. 

## Métodos:
Cartão de crédito, Débito, Boleto Registrado e transferências bancárias (Itaú e Bradesco)

## Bandeiras:
Visa, Mastercard, American Express, Hipercard, JCB, Hiper, Elo entre outras.

## Adquirentes:
Cielo, Rede, Getnet, Elavon, Global Payments, Granito, Stone, First Data, Getnet, Adiq e Zoop. Bancos: Itau, Banco do Brasil e Bradesco

## Antifraudes:
FControl, ClearSale e Konduto


## Instalação
 
    composer require azpay/magento2x:latest
    bin/magento cache:clean
    bin/magento setup:upgrade
    bin/magento setup:di:compile
    
## Configuração

Stores -> Configuration -> Sales -> Payments Methods

- Alterar a chave para o ambiente correto
- Ativar os métodos de pagamentos desejados
- Pronto! 

## Suporte e dúvidas
Atendimento em horário comercial de segunda a sexta-feira das 9h às 18h

- Atendimento pelo canal online ou chat
http://help.azpay.com.br/

- Atendimento por e-mail
atendimento@azpay.com.br

- Atendimento por telefone em horário comercial
+55 (11) 99268 – 6502 (WhatsApp)
