<?php

require_once dirname(__FILE__) . '/../Connector/Invoice/InvoiceBill.php';

/**
 * Description of StrategyBill
 *
 * @author bbojanowicz
 */
class PowerMedia_Ifirma_Model_Builder_StrategyBill extends PowerMedia_Ifirma_Model_Builder_StrategyAbstract{
	
	public function makeInvoice() {
		$bill = new ifirma\InvoiceBill();
		
		$bill->{ifirma\InvoiceBill::KEY_KONTRAHENT} = $this->createContractorObject();
		
		$bill->{ifirma\InvoiceBill::KEY_FORMAT_DATY_SPRZEDAZY} = ifirma\InvoiceBill::DEFAULT_VALUE_FORMAT_DATY_SPRZEDAZY;
		$bill->{ifirma\InvoiceBill::KEY_DATA_WYSTAWIENIA} = date('Y-m-d');
		$bill->{ifirma\InvoiceBill::KEY_DATA_SPRZEDAZY} = $this->getOrderDate();
		$bill->{ifirma\InvoiceBill::KEY_ZAPLACONO} = 0;
		$bill->{ifirma\InvoiceBill::KEY_UWAGI} = $this->getOrderNumber();
		$bill->{ifirma\InvoiceBill::KEY_SPOSOB_ZAPLATY} = $this->getPaymentType();
		$bill->{ifirma\InvoiceBill::KEY_MIEJSCE_WYSTAWIENIA} = $this->_getConfig()->{ifirma\Config::API_MIEJSCE_WYSTAWIENIA};
		$bill->{ifirma\InvoiceBill::KEY_WPIS_DO_KPIR} = ifirma\InvoiceBill::DEFAULT_VALUE_WPIS_DO_KPIR;
		$bill->{ifirma\InvoiceBill::KEY_NAZWA_SERII_NUMERACJI} = $this->_getConfig()->{ifirma\Config::API_NAZWA_SERII_NUMERACJI};
		
		foreach($this->getOrder()->getAllVisibleItems() as $item){
			$bill->addInvoiceBillPosition($this->createInvoiceBillPosition($item));
		}
		
		if($this->isNecessaryToAddShippingPosition()){
			$bill->addInvoiceBillPosition($this->createInvoiceBillPositionShippingCost());
		}
		
		if($this->_getConfig()->{ifirma\Config::API_RYCZALT}){
			$ew = $this->_getConfig()->{ifirma\Config::API_RYCZALT_WPIS_DO_EWIDENCJI};
			$bill->{ifirma\InvoiceBill::KEY_WPIS_DO_EWIDENCJI} = $ew ? 'TAK' : 'NIE';
			$bill->{ifirma\InvoiceBill::KEY_WPIS_DO_KPIR} = ifirma\InvoiceBill::DEFAULT_VALUE_WPIS_DO_KPIR_NIE;
		}
		
		return $bill;
	}
	
	private function createInvoiceBillPosition($item){
		$billPosition = new ifirma\InvoiceBillPosition();
		
		$billPosition->{ifirma\InvoiceBillPosition::KEY_JEDNOSTKA} = self::DEFAULT_UNIT_NAME;
		$billPosition->{ifirma\InvoiceBillPosition::KEY_ILOSC} = $item->getQtyOrdered();
		$billPosition->{ifirma\InvoiceBillPosition::KEY_CENA_JEDNOSTKOWA} = $this->roundPrice($item->getPriceInclTax());
		$billPosition->{ifirma\InvoiceBillPosition::KEY_NAZWA_PELNA} = $item->getName();
		if($this->_getConfig()->{ifirma\Config::API_RYCZALT}){
			$billPosition->{ifirma\InvoiceBillPosition::KEY_RYCZALT_STAWKA} = $this->_getConfig()->{ifirma\Config::API_RYCZALT_RATE};
		}
		return $billPosition;
	}
	
	private function createInvoiceBillPositionShippingCost() {
		$billPosition = new ifirma\InvoiceBillPosition();
		
		$billPosition->{ifirma\InvoiceBillPosition::KEY_JEDNOSTKA} = self::DEFAULT_SHIPPING_UNIT_NAME;
		$billPosition->{ifirma\InvoiceBillPosition::KEY_ILOSC} = 1;
		$billPosition->{ifirma\InvoiceBillPosition::KEY_CENA_JEDNOSTKOWA} = $this->roundPrice($this->getOrder()->getShippingInclTax());
		$billPosition->{ifirma\InvoiceBillPosition::KEY_NAZWA_PELNA} = self::SHIPPING_NAME_PREFIX . $this->getOrder()->getShippingDescription();
		if($this->_getConfig()->{ifirma\Config::API_RYCZALT}){
			$billPosition->{ifirma\InvoiceBillPosition::KEY_RYCZALT_STAWKA} = $this->_getConfig()->{ifirma\Config::API_RYCZALT_RATE};
		}
		return $billPosition;
	}
}

