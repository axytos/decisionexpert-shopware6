<?php declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Tests\Core;

use Axytos\DecisionExpert\Shopware\Core\PaymentControlOrderDataFactory;
use Axytos\Shopware\DataMapping\CustomerDataDtoFactory;
use Axytos\Shopware\DataMapping\DeliveryAddressDtoFactory;
use Axytos\Shopware\DataMapping\InvoiceAddressDtoFactory;
use Axytos\Shopware\DataMapping\PaymentControlBasketDtoFactory;
use Axytos\Shopware\DataMapping\PaymentControlBasketPositionDtoCollectionFactory;
use Axytos\Shopware\DataMapping\PaymentControlBasketPositionDtoFactory;
use Axytos\Shopware\ValueCalculation\PositionNetPriceCalculator;
use Axytos\Shopware\ValueCalculation\PositionProductIdCalculator;
use Axytos\Shopware\ValueCalculation\PositionProductNameCalculator;
use Axytos\Shopware\ValueCalculation\PositionTaxPercentCalculator;
use Axytos\Shopware\ValueCalculation\PromotionIdentifierCalculator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\System\Country\Aggregate\CountryState\CountryStateEntity;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\Salutation\SalutationEntity;

class PaymentControlOrderDataFactoryTest extends TestCase
{
    private PaymentControlOrderDataFactory $sut;

    public function setUp(): void
    {
        $this->sut = new PaymentControlOrderDataFactory(
            new CustomerDataDtoFactory(),
            new DeliveryAddressDtoFactory(),
            new InvoiceAddressDtoFactory(),
            new PaymentControlBasketDtoFactory(
                new PaymentControlBasketPositionDtoCollectionFactory(
                    new PaymentControlBasketPositionDtoFactory(
                        new PositionNetPriceCalculator(),
                        new PositionTaxPercentCalculator(),
                        new PositionProductIdCalculator(new PromotionIdentifierCalculator()),
                        new PositionProductNameCalculator(new PromotionIdentifierCalculator()),
                    )
                )
            )
        );
    }

    public function test_create_maps_paymentMethodId_correctly() : void
    {
        $paymentMethodId = 'paymentMethodId';
        /** @var SalesChannelContext&MockObject $salesChannelContext */
        $salesChannelContext = $this->createMock(SalesChannelContext::class);
        /** @var OrderEntity&MockObject $order */
        $order = $this->createMock(OrderEntity::class);
        /** @var PaymentMethodEntity&MockObject $paymentMethod */
        $paymentMethod = $this->createMock(PaymentMethodEntity::class);

        $salesChannelContext
            ->method('getPaymentMethod')
            ->willReturn($paymentMethod);

        $paymentMethod
            ->method('getId')
            ->willReturn($paymentMethodId);

        $actual = $this->sut->create($order, $salesChannelContext);

        $this->assertSame($paymentMethodId, $actual->paymentMethodId);
    }

    public function test_create_maps_deliveryAddress_correctly() : void
    {
        /** @var SalesChannelContext&MockObject $salesChannelContext */
        $salesChannelContext = $this->createMock(SalesChannelContext::class);
        /** @var OrderEntity&MockObject $order */
        $order = $this->createMock(OrderEntity::class);
        /** @var OrderDeliveryCollection&MockObject $deliveries */
        $deliveries = $this->createMock(OrderDeliveryCollection::class);
        /** @var OrderDeliveryEntity&MockObject $deliveryElement */
        $deliveryElement = $this->createMock(OrderDeliveryEntity::class);
        $deliveryElements = [$deliveryElement];
        /** @var OrderAddressEntity&MockObject $shippingOrderAddress */
        $shippingOrderAddress = $this->createMock(OrderAddressEntity::class);
        /** @var CountryEntity&MockObject $country */
        $country = $this->createMock(CountryEntity::class);
        /** @var CountryStateEntity&MockObject $countryState */
        $countryState = $this->createMock(CountryStateEntity::class);
        /** @var SalutationEntity&MockObject $salutation */
        $salutation = $this->createMock(SalutationEntity::class);
        /** @var CalculatedTax&MockObject $calculatedTax */
        $calculatedTax = $this->createMock(CalculatedTax::class);



        $street = 'street';
        $city = 'city';
        $company = 'company';
        $firstname = 'firstname';
        $lastname = 'lastname';
        $zipCode = 'zipCode';
        $vatId = 'vatId';
        $countryIso = 'countryIso';
        $stateName = 'stateName';
        $salutationDisplayName = 'salutationDisplayName';

        $order
            ->method('getDeliveries')
            ->willReturn($deliveries);
        
        $deliveries
            ->method('getElements')
            ->willReturn($deliveryElements);

        $deliveryElement
            ->method('getShippingOrderAddress')
            ->willReturn($shippingOrderAddress);

        $shippingOrderAddress
            ->method('getStreet')
            ->willReturn($street);
        
        $shippingOrderAddress
            ->method('getCity')
            ->willReturn($city);
        
        $shippingOrderAddress
            ->method('getCompany')
            ->willReturn($company);
    
        $shippingOrderAddress
            ->method('getFirstName')
            ->willReturn($firstname);

        $shippingOrderAddress
            ->method('getLastName')
            ->willReturn($lastname);

        $shippingOrderAddress
            ->method('getZipcode')
            ->willReturn($zipCode);

        $shippingOrderAddress
            ->method('getVatId')
            ->willReturn($vatId);

        $shippingOrderAddress
            ->method('getCountry')
            ->willReturn($country);

        $country
            ->method('getIso')
            ->willReturn($countryIso);

        $shippingOrderAddress
            ->method('getCountryState')
            ->willReturn($countryState);
        
        $countryState
            ->method('getName')
            ->willReturn($stateName);

        $shippingOrderAddress
            ->method('getSalutation')
            ->willReturn($salutation);
        
        $salutation
            ->method('getDisplayName')
            ->willReturn($salutationDisplayName);

        $calculatedTax
            ->method("getTaxRate")
            ->willReturn(19.0);

        

        $actual = $this->sut->create($order, $salesChannelContext);

        $this->assertSame($street, $actual->deliveryAddress->addressLine1);
        $this->assertSame($city, $actual->deliveryAddress->city);
        $this->assertSame($company, $actual->deliveryAddress->company);
        $this->assertSame($firstname, $actual->deliveryAddress->firstname);
        $this->assertSame($lastname, $actual->deliveryAddress->lastname);
        $this->assertSame($zipCode, $actual->deliveryAddress->zipCode);
        $this->assertSame($vatId, $actual->deliveryAddress->vatId);
        $this->assertSame($countryIso, $actual->deliveryAddress->country);
        $this->assertSame($stateName, $actual->deliveryAddress->region);
        $this->assertSame($salutationDisplayName, $actual->deliveryAddress->salutation);
    }

    public function test_create_maps_invoice_correctly() : void
    {
        /** @var SalesChannelContext&MockObject $salesChannelContext */
        $salesChannelContext = $this->createMock(SalesChannelContext::class);
        /** @var OrderEntity&MockObject $order */
        $order = $this->createMock(OrderEntity::class);
        /** @var OrderAddressEntity&MockObject $billingAddress */
        $billingAddress = $this->createMock(OrderAddressEntity::class);
        /** @var CountryEntity&MockObject $country */
        $country = $this->createMock(CountryEntity::class);
        /** @var CountryStateEntity&MockObject $countryState */
        $countryState = $this->createMock(CountryStateEntity::class);
        /** @var SalutationEntity&MockObject $salutation */
        $salutation = $this->createMock(SalutationEntity::class);

        $street = 'street';
        $city = 'city';
        $company = 'company';
        $firstname = 'firstname';
        $lastname = 'lastname';
        $zipCode = 'zipCode';
        $vatId = 'vatId';
        $countryIso = 'countryIso';
        $stateName = 'stateName';
        $salutationDisplayName = 'salutationDisplayName';

        $order
            ->method('getBillingAddress')
            ->willReturn($billingAddress);

        $billingAddress
            ->method('getStreet')
            ->willReturn($street);
        
        $billingAddress
            ->method('getCity')
            ->willReturn($city);
        
        $billingAddress
            ->method('getCompany')
            ->willReturn($company);
    
        $billingAddress
            ->method('getFirstName')
            ->willReturn($firstname);

        $billingAddress
            ->method('getLastName')
            ->willReturn($lastname);

        $billingAddress
            ->method('getZipcode')
            ->willReturn($zipCode);

        $billingAddress
            ->method('getVatId')
            ->willReturn($vatId);

        $billingAddress
            ->method('getCountry')
            ->willReturn($country);

        $country
            ->method('getIso')
            ->willReturn($countryIso);

        $billingAddress
            ->method('getCountryState')
            ->willReturn($countryState);
        
        $countryState
            ->method('getName')
            ->willReturn($stateName);

        $billingAddress
            ->method('getSalutation')
            ->willReturn($salutation);
        
        $salutation
            ->method('getDisplayName')
            ->willReturn($salutationDisplayName);

        $actual = $this->sut->create($order, $salesChannelContext);

        $this->assertSame($street, $actual->invoiceAddress->addressLine1);
        $this->assertSame($city, $actual->invoiceAddress->city);
        $this->assertSame($company, $actual->invoiceAddress->company);
        $this->assertSame($firstname, $actual->invoiceAddress->firstname);
        $this->assertSame($lastname, $actual->invoiceAddress->lastname);
        $this->assertSame($zipCode, $actual->invoiceAddress->zipCode);
        $this->assertSame($vatId, $actual->invoiceAddress->vatId);
        $this->assertSame($countryIso, $actual->invoiceAddress->country);
        $this->assertSame($stateName, $actual->invoiceAddress->region);
        $this->assertSame($salutationDisplayName, $actual->invoiceAddress->salutation);
    }

    public function test_create_maps_basket_correctly() : void
    {
        /** @var SalesChannelContext&MockObject $salesChannelContext */
        $salesChannelContext = $this->createMock(SalesChannelContext::class);
        /** @var OrderEntity&MockObject $order */
        $order = $this->createMock(OrderEntity::class);
        /** @var CurrencyEntity&MockObject $currency */
        $currency = $this->createMock(CurrencyEntity::class);
        /** @var OrderLineItemCollection&MockObject $lineItems */
        $lineItems = $this->createMock(OrderLineItemCollection::class);
        /** @var OrderLineItemEntity&MockObject $lineItemElement1 */
        $lineItemElement1 = $this->createMock(OrderLineItemEntity::class);
        $lineItemElement1->method('getType')->willReturn(LineItem::PRODUCT_LINE_ITEM_TYPE);
        /** @var OrderLineItemEntity&MockObject $lineItemElement2 */
        $lineItemElement2 = $this->createMock(OrderLineItemEntity::class);
        $lineItemElement2->method('getType')->willReturn(LineItem::PRODUCT_LINE_ITEM_TYPE);
        $lineItemElements = [$lineItemElement1, $lineItemElement2];

        /** @var ProductEntity&MockObject $lineItem1Product */
        $lineItem1Product = $this->createMock(ProductEntity::class);
        /** @var ProductEntity&MockObject $lineItem2Product */
        $lineItem2Product = $this->createMock(ProductEntity::class);

        /** @var CalculatedPrice&MockObject $lineItem1Price */
        $lineItem1Price = $this->createMock(CalculatedPrice::class);
        /** @var CalculatedTaxCollection&MockObject $lineItem1CalculatedTaxes */
        $lineItem1CalculatedTaxes = $this->createMock(CalculatedTaxCollection::class);
        /** @var CalculatedTax&MockObject $lineItem1Vat */
        $lineItem1Vat = $this->createMock(CalculatedTax::class);
        $lineItem1CalculatedTaxElements = [$lineItem1Vat];

        /** @var CalculatedPrice&MockObject $lineItem2Price */
        $lineItem2Price = $this->createMock(CalculatedPrice::class);
        /** @var CalculatedTaxCollection&MockObject $lineItem2CalculatedTaxes */
        $lineItem2CalculatedTaxes = $this->createMock(CalculatedTaxCollection::class);
        /** @var CalculatedTax&MockObject $lineItem2Vat1 */
        $lineItem2Vat1 = $this->createMock(CalculatedTax::class);
        /** @var CalculatedTax&MockObject $lineItem2Vat2 */
        $lineItem2Vat2 = $this->createMock(CalculatedTax::class);
        $lineItem2CalculatedTaxElements = [$lineItem2Vat1, $lineItem2Vat2];

        /** @var CalculatedPrice&MockObject $shippingCosts */
        $shippingCosts = $this->createMock(CalculatedPrice::class);
        /** @var CalculatedTax&MockObject $calculatedTax */
        $calculatedTax = $this->createMock(CalculatedTax::class);

        $isoCode = 'EUR';
        $amountTotal = 39.99;
        $amountNet = 33.61;

        $lineItem1TotalPrice = 19.99;
        $lineItem1Label = 'lineItem1Label';
        $lineItem1Quantity = 1;
        $lineItem1VatTax = 3.19;
        $lineItem1VatTaxRate = 19.00;

        $lineItem2TotalPrice = 20.00;
        $lineItem2Label = 'lineItem2Label';
        $lineItem2Quantity = 1;
        $lineItem2Vat1Tax = 1.68;
        $lineItem2Vat1TaxRate = 10.00;
        $lineItem2Vat1Price = ($lineItem2TotalPrice / 2);
        $lineItem2Vat2Tax = 1.51;
        $lineItem2Vat2TaxRate = 9.00;
        $lineItem2Vat2Price = ($lineItem2TotalPrice / 2);

        $lineItem1ProductNumber = '10001';
        $lineItem2ProductNumber = '10002';

        $lineItem1Product
            ->method('getProductNumber')
            ->willReturn($lineItem1ProductNumber);

        $lineItem2Product
            ->method('getProductNumber')
            ->willReturn($lineItem2ProductNumber);
        
        $lineItemElement1
            ->method('getUniqueIdentifier')
            ->willReturn('LineItem1');

        $lineItemElement2
            ->method('getUniqueIdentifier')
            ->willReturn('LineItem2');

        $lineItems = new OrderLineItemCollection($lineItemElements);

        $order
            ->method('getCurrency')
            ->willReturn($currency);

        $currency
            ->method('getIsoCode')
            ->willReturn($isoCode);
        
        $order
            ->method('getAmountTotal')
            ->willReturn($amountTotal);
    
        $order
            ->method('getAmountNet')
            ->willReturn($amountNet);

        $order
            ->method('getLineItems')
            ->willReturn($lineItems);
        
        $order->method("getShippingCosts")->willReturn($shippingCosts);

        $shippingCosts->method("getCalculatedTaxes")->willReturn(new CalculatedTaxCollection([$calculatedTax]));

        $calculatedTax->method("getTaxRate")->willReturn(19.0);

        $lineItemElement1
            ->method('getProduct')
            ->willReturn($lineItem1Product);
        
        $lineItemElement1
            ->method('getTotalPrice')
            ->willReturn($lineItem1TotalPrice);

        $lineItemElement1
            ->method('getLabel')
            ->willReturn($lineItem1Label);

        $lineItemElement1
            ->method('getQuantity')
            ->willReturn($lineItem1Quantity);

        $lineItemElement1
            ->method('getPrice')
            ->willReturn($lineItem1Price);
        
        $lineItem1Price
            ->method('getTotalPrice')
            ->willReturn($lineItem1TotalPrice);

        $lineItem1Vat
            ->method('getTax')
            ->willReturn($lineItem1VatTax);
        
        $lineItem1Vat
            ->method('getTaxRate')
            ->willReturn($lineItem1VatTaxRate);
        
        $lineItem1Vat
            ->method('getPrice')
            ->willReturn($lineItem1TotalPrice);
        
        $lineItem1Price
            ->method('getCalculatedTaxes')
            ->willReturn(new CalculatedTaxCollection($lineItem1CalculatedTaxElements));

        $lineItemElement2
            ->method('getProduct')
            ->willReturn($lineItem2Product);
        
        $lineItemElement2
            ->method('getTotalPrice')
            ->willReturn($lineItem2TotalPrice);

        $lineItemElement2
            ->method('getLabel')
            ->willReturn($lineItem2Label);

        $lineItemElement2
            ->method('getQuantity')
            ->willReturn($lineItem2Quantity);

        $lineItemElement2
            ->method('getPrice')
            ->willReturn($lineItem2Price);
        
        $lineItem2Price
            ->method('getTotalPrice')
            ->willReturn($lineItem2TotalPrice);

        $lineItem2Vat1
            ->method('getTax')
            ->willReturn($lineItem2Vat1Tax);
        
        $lineItem2Vat1
            ->method('getTaxRate')
            ->willReturn($lineItem2Vat1TaxRate);
        
        $lineItem2Vat1
            ->method('getPrice')
            ->willReturn($lineItem2Vat1Price);
        
        $lineItem2Vat2
            ->method('getTax')
            ->willReturn($lineItem2Vat2Tax);
        
        $lineItem2Vat2
            ->method('getTaxRate')
            ->willReturn($lineItem2Vat2TaxRate);
        
        $lineItem2Vat1
            ->method('getPrice')
            ->willReturn($lineItem2Vat2Price);
        
        $lineItem2Price
            ->method('getCalculatedTaxes')
            ->willReturn(new CalculatedTaxCollection($lineItem2CalculatedTaxElements));

        
        
        $actual = $this->sut->create($order, $salesChannelContext);

        $this->assertSame($isoCode, $actual->basket->currency);
        $this->assertSame($amountTotal, $actual->basket->grossTotal);
        $this->assertSame($amountNet, $actual->basket->netTotal);
        $this->assertSame($lineItem1TotalPrice, $actual->basket->positions[0]->grossPositionTotal);
        $this->assertSame($lineItem1ProductNumber, $actual->basket->positions[0]->productId);
        $this->assertSame($lineItem1Label, $actual->basket->positions[0]->productName);
        $this->assertSame($lineItem1Quantity, $actual->basket->positions[0]->quantity);
        $this->assertSame(round($lineItem1TotalPrice - $lineItem1VatTax, 2), $actual->basket->positions[0]->netPositionTotal);
        $this->assertSame($lineItem1VatTaxRate, $actual->basket->positions[0]->taxPercent);
        $this->assertSame($lineItem2TotalPrice, $actual->basket->positions[1]->grossPositionTotal);
        $this->assertSame($lineItem2ProductNumber, $actual->basket->positions[1]->productId);
        $this->assertSame($lineItem2Label, $actual->basket->positions[1]->productName);
        $this->assertSame($lineItem2Quantity, $actual->basket->positions[1]->quantity);
        $this->assertSame($lineItem2TotalPrice - $lineItem2Vat1Tax - $lineItem2Vat2Tax, $actual->basket->positions[1]->netPositionTotal);
        $this->assertSame($lineItem2Vat1TaxRate + $lineItem2Vat2TaxRate, $actual->basket->positions[1]->taxPercent);
        $this->assertSame("Shipping", $actual->basket->positions[2]->productName);
        $this->assertSame("0", $actual->basket->positions[2]->productId);

    }

    public function test_create_maps_personalData_correctly_without_existing_customer() : void
    {
        /** @var SalesChannelContext&MockObject $salesChannelContext */
        $salesChannelContext = $this->createMock(SalesChannelContext::class);
        /** @var OrderEntity&MockObject $order */
        $order = $this->createMock(OrderEntity::class);
        /** @var OrderCustomerEntity&MockObject $orderCustomer */
        $orderCustomer = $this->createMock(OrderCustomerEntity::class);

        $email = 'email';
        $customerNumber = 'customerNumber';
        $customerId = 'customerId';

        $order
            ->method('getOrderCustomer')
            ->willReturn($orderCustomer);
        
        $orderCustomer
            ->method('getEmail')
            ->willReturn($email);

        $orderCustomer
            ->method('getCustomerId')
            ->willReturn($customerId);

        $orderCustomer
            ->method('getCustomerNumber')
            ->willReturn($customerNumber);
        
        $orderCustomer
            ->method('getCustomer')
            ->willReturn(null);
        
        $actual = $this->sut->create($order, $salesChannelContext);

        $this->assertSame($email, $actual->personalData->email);
        $this->assertSame($customerNumber.'-'.$customerId, $actual->personalData->externalCustomerId);
        $this->assertSame(null, $actual->personalData->dateOfBirth);
    }

    public function test_create_maps_personalData_correctly_for_existing_customer_without_birthdate() : void
    {
        /** @var SalesChannelContext&MockObject $salesChannelContext */
        $salesChannelContext = $this->createMock(SalesChannelContext::class);
        /** @var OrderEntity&MockObject $order */
        $order = $this->createMock(OrderEntity::class);
        /** @var OrderCustomerEntity&MockObject $orderCustomer */
        $orderCustomer = $this->createMock(OrderCustomerEntity::class);
        /** @var CustomerEntity&MockObject $customer */
        $customer = $this->createMock(CustomerEntity::class);

        $email = 'email';
        $customerNumber = 'customerNumber';
        $customerId = 'customerId';

        $order
            ->method('getOrderCustomer')
            ->willReturn($orderCustomer);
        
        $orderCustomer
            ->method('getEmail')
            ->willReturn($email);

        $orderCustomer
            ->method('getCustomerId')
            ->willReturn($customerId);

        $orderCustomer
            ->method('getCustomerNumber')
            ->willReturn($customerNumber);
        
        $orderCustomer
            ->method('getCustomer')
            ->willReturn($customer);

        $customer
            ->method('getBirthDay')
            ->willReturn(null);
        
        $actual = $this->sut->create($order, $salesChannelContext);

        $this->assertSame($email, $actual->personalData->email);
        $this->assertSame($customerNumber.'-'.$customerId, $actual->personalData->externalCustomerId);
        $this->assertSame(null, $actual->personalData->dateOfBirth);
    }

    public function test_create_maps_personalData_correctly_for_existing_customer_with_birthdate_date_time_type() : void
    {
        /** @var SalesChannelContext&MockObject $salesChannelContext */
        $salesChannelContext = $this->createMock(SalesChannelContext::class);
        /** @var OrderEntity&MockObject $order */
        $order = $this->createMock(OrderEntity::class);
        /** @var OrderCustomerEntity&MockObject $orderCustomer */
        $orderCustomer = $this->createMock(OrderCustomerEntity::class);
        /** @var CustomerEntity&MockObject $customer */
        $customer = $this->createMock(CustomerEntity::class);
        /** @var \DateTime $dateTime */
        $dateTime = new \DateTime();

        $email = 'email';
        $customerNumber = 'customerNumber';
        $customerId = 'customerId';

        $order
            ->method('getOrderCustomer')
            ->willReturn($orderCustomer);
        
        $orderCustomer
            ->method('getEmail')
            ->willReturn($email);

        $orderCustomer
            ->method('getCustomerId')
            ->willReturn($customerId);

        $orderCustomer
            ->method('getCustomerNumber')
            ->willReturn($customerNumber);
        
        $orderCustomer
            ->method('getCustomer')
            ->willReturn($customer);

        $customer
            ->method('getBirthDay')
            ->willReturn($dateTime);
        
        $actual = $this->sut->create($order, $salesChannelContext);

        $this->assertSame($email, $actual->personalData->email);
        $this->assertSame($customerNumber.'-'.$customerId, $actual->personalData->externalCustomerId);
        $this->assertNotNull($actual->personalData->dateOfBirth);
        $this->assertSame($dateTime->getTimestamp(), $actual->personalData->dateOfBirth->getTimestamp());
    }
    
    public function test_create_maps_personalData_correctly_for_existing_customer_with_birthdate_date_time_immutable_type() : void
    {
        /** @var SalesChannelContext&MockObject $salesChannelContext */
        $salesChannelContext = $this->createMock(SalesChannelContext::class);
        /** @var OrderEntity&MockObject $order */
        $order = $this->createMock(OrderEntity::class);
        /** @var OrderCustomerEntity&MockObject $orderCustomer */
        $orderCustomer = $this->createMock(OrderCustomerEntity::class);
        /** @var CustomerEntity&MockObject $customer */
        $customer = $this->createMock(CustomerEntity::class);
        /** @var \DateTimeImmutable $dateTimeImmutable */
        $dateTimeImmutable = new \DateTimeImmutable();

        $email = 'email';
        $customerNumber = 'customerNumber';
        $customerId = 'customerId';
        $company = 'company';

        $order
            ->method('getOrderCustomer')
            ->willReturn($orderCustomer);
        
        $orderCustomer
            ->method('getEmail')
            ->willReturn($email);
        
        $orderCustomer
            ->method('getCompany')
            ->willReturn($company);

        $orderCustomer
            ->method('getCustomerId')
            ->willReturn($customerId);

        $orderCustomer
            ->method('getCustomerNumber')
            ->willReturn($customerNumber);
        
        $orderCustomer
            ->method('getCustomer')
            ->willReturn($customer);

        $customer
            ->method('getBirthDay')
            ->willReturn($dateTimeImmutable);
        
        $actual = $this->sut->create($order, $salesChannelContext);

        $this->assertSame($email, $actual->personalData->email);
        /** @phpstan-ignore-next-line */
        $this->assertSame($company, $actual->personalData->company->name);
        $this->assertSame($customerNumber.'-'.$customerId, $actual->personalData->externalCustomerId);
        $this->assertNotNull($actual->personalData->dateOfBirth);
        $this->assertSame($dateTimeImmutable->getTimestamp(), $actual->personalData->dateOfBirth->getTimestamp());
    }

}
