<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_kar_report_payment extends CI_Model {

	public function annual($year)
  {
		$billing = $this->db->query(
			"SELECT
				MONTH(billing_date_in) AS tx_month,
				YEAR(billing_date_in) AS tx_year,
				SUM(billing_subtotal) AS billing_subtotal,
				SUM(billing_tax) AS billing_tax,
				SUM(billing_total) AS billing_total,
				SUM(billing_down_payment) AS billing_down_payment
			FROM kar_billing
			WHERE
				billing_date_in LIKE '$year%' AND
				billing_status = '2'
			GROUP BY MONTH(billing_date_in)
			ORDER BY billing_date_in ASC"
		)->result();

		return $billing;
	}

	public function monthly($month)
  {
		$billing = $this->db->query(
			"SELECT
				billing_date_in,
				SUM(billing_subtotal) AS billing_subtotal,
				SUM(billing_tax) AS billing_tax,
				SUM(billing_total) AS billing_total,
				SUM(billing_down_payment) AS billing_down_payment
			FROM kar_billing
			WHERE
				billing_date_in LIKE '$month%' AND
				billing_status = '2'
			GROUP BY billing_date_in
			ORDER BY billing_date_in DESC"
		)->result();

		return $billing;
	}

	public function weekly($date_start, $date_end)
	{
		$billing = $this->db->query(
			"SELECT
				billing_date_in,
				SUM(billing_subtotal) AS billing_subtotal,
				SUM(billing_tax) AS billing_tax,
				SUM(billing_total) AS billing_total,
				SUM(billing_down_payment) AS billing_down_payment
			FROM kar_billing
			WHERE
				billing_date_in >= '$date_start' AND
				billing_date_in <= '$date_end' AND
				billing_status = '2'
			GROUP BY billing_date_in
			ORDER BY billing_date_in DESC"
		)->result();

		return $billing;
	}

	public function daily($date)
  {
		$billing = $this->db->query(
			"SELECT
				*
			FROM kar_billing
			WHERE
				billing_date_in = '$date' AND
				billing_status = '2'
			ORDER BY billing_receipt_no ASC"
		)->result();

		return $billing;
	}

	public function range($date_start, $date_end)
	{
		$billing = $this->db->query(
			"SELECT
				billing_date_in,
				SUM(billing_subtotal) AS billing_subtotal,
				SUM(billing_tax) AS billing_tax,
				SUM(billing_total) AS billing_total,
				SUM(billing_down_payment) AS billing_down_payment
			FROM kar_billing
			WHERE
				billing_date_in >= '$date_start' AND
				billing_date_in <= '$date_end' AND
				billing_status = '2'
			GROUP BY billing_date_in
			ORDER BY billing_date_in DESC"
		)->result();

		return $billing;
	}

	public function detail($billing_id)
	{
		$billing = $this->db
			->select('kar_billing')
			->where('billing_id', $billing_id)
			->get('kar_billing')->row();
		$billing->detail = $this->db->where('billing_id', $billing_id)->get('kar_billing_detail')->result();
		$billing->buyget = $this->db
			->select('kar_billing_buyget.*,res_item.item_name,res_promo.promo_name')
			->join('res_item', 'kar_billing_buyget.get_item_id = res_item.item_id')
			->join('res_promo_buyget', 'kar_billing_buyget.promo_buyget_id = res_promo_buyget.promo_buyget_id')
			->join('res_promo', 'res_promo_buyget.promo_id = res_promo.promo_id')
			->where('billing_id', $billing_id)
			->get('kar_billing_buyget')->result();

		$billing->buyitem = $this->db
			->select('kar_billing_buyitem.*,res_promo.promo_name')
			->join('res_promo_buyitem', 'kar_billing_buyitem.promo_buyitem_id = res_promo_buyitem.promo_buyitem_id')
			->join('res_promo', 'res_promo_buyitem.promo_id = res_promo.promo_id')
			->where('billing_id', $billing_id)
			->get('kar_billing_buyitem')->result();

		$billing->buyall = $this->db
		  ->select('kar_billing_buyall.*,res_promo.promo_name')
		  ->join('res_promo_buyall', 'kar_billing_buyall.promo_buyall_id = res_promo_buyall.promo_buyall_id')
		  ->join('res_promo', 'res_promo_buyall.promo_id = res_promo.promo_id')
		  ->where('billing_id', $billing_id)
		  ->get('kar_billing_buyall')->result();

		return $billing;
	}

}
