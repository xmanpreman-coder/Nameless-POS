=== DOCUMENT CATEGORIES ===
1|Expenses|Document.Category.Expenses
2|Sales|Document.Category.Sales
3|Inventory|Document.Category.Inventory
4|Loss|Document.Category.Loss

=== DOCUMENT TYPES ===
1|Purchase|100|1|1|1|0|Purchase|2|Document.Category.Expenses.Purchase
2|Sales|200|2|1|2|0|Invoice|1|Document.Category.Sales.Sales
3|Inventory Count|300|3|1|1|1|InventoryCount|0|Document.Category.Inventory.InventoryCount
4|Refund|220|2|1|1|0|Refund|1|Document.Category.Sales.Refund
5|Stock Return|120|1|1|2|0|StockReturn|2|Document.Category.Expenses.StockReturn
6|Loss And Damage|400|4|1|2|2|LossAndDamage|0|Document.Category.Loss.LossAndDamage
7|Proforma|230|2|1|0|0|Proforma|1|Document.Category.Sales.Proforma

=== POS PRINTER SELECTION (Key Features) ===
1|ReceiptPrinter|POS-80|1
2|CreditPaymentNote||0
3|Estimate||0
4|KitchenTicket||0
5|Service||0

=== APPLICATION PROPERTIES (Settings) ===
Application.Api.BaseUrl|https://api.aronium.com/api
Application.Id|debd785e-bb17-4879-ac5a-c0014220948d
Application.User.Email|rama.monkedi@gmail.com
GuidedTour.Management.Products|1
GuidedTour.Management.Products.Add|1
GuidedTour.Layout.SearchBox|1
MovingAveragePrice.Enabled|False
Application.OnboardingStatus|2

=== SECURITY KEYS (Permissions) ===
Settings|0
Order.Void|0
Order.Item.Void|0
Order.Estimate|0
Order.Estimate.Clear|0
Order.Transfer|0
Payment.Discount|0
