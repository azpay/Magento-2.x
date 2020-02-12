# Módulo Gateway para Magento 2

Testado na versão 2.3.1

## Instalação
 
    composer require azpay/magento2x:dev-master
    bin/magento cache:clean
    bin/magento setup:upgrade
    bin/magento setup:di:compile
    
## Configuração

Stores -> Configuration -> Sales -> Payments Methods

- Alterar a chave para o ambiente correto
- Ativar os métodos de pagamentos desejados
- Pronto! 
