## Cotización del Dólar
### Script PHP que permite obtener la cotización del Dólar tanto en Pesos argentinos como en Pesos mexicanos.

### Utilización:

	Ejecutar "php dollar.php [amount] [currency]" mediante terminal.
	
	Ej. "php dollar.php 9999.80 ARS"
	
### Parámetros:	
	
	[amount] Monto en la divisa de origen a cotizar en Dólares.
		Precisión de 2 dígitos decimales. Acepta "." como separador decimal.
		Si se introducen más de 2 dígitos decimales serán redondeados a 2.
			  
	[currency] Divisa de origen. "ARS" para Pesos argentinos, "MXN" para Pesos mexicanos.
	
### Comando Ayuda:

	Ejecutar "php dollar.php -h" mediante terminal.
