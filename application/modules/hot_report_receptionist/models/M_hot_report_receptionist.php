<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_hot_report_receptionist extends CI_Model {

	public function annual($year,$user_id)
  {
		$billing = $this->db->query(
			"SELECT
				MONTH(billing_date_in) AS tx_month,
				YEAR(billing_date_in) AS tx_year,
				SUM(billing_subtotal) AS billing_subtotal,
				SUM(billing_discount) AS billing_discount,
				SUM(billing_discount_custom) AS billing_discount_custom,
				SUM(billing_denda) AS billing_denda,
				SUM(billing_tax) AS billing_tax,
				SUM(billing_service) AS billing_service,
				SUM(billing_other) AS billing_other,
				SUM(billing_total) AS billing_total
			FROM hot_billing
			WHERE
				user_id = '$user_id' AND
				billing_date_in LIKE '$year%' AND
				billing_status != '0' AND
				billing_status != '-1'
			GROUP BY MONTH(billing_date_in)
			ORDER BY billing_date_in ASC"
		)->result();

		return $billing;
	}

	public function monthly($month,$user_id)
  {
		$billing = $this->db->query(
			"SELECT
				billing_date_in,
				SUM(billing_subtotal) AS billing_subtotal,
				SUM(billing_discount) AS billing_discount,
				SUM(billing_discount_custom) AS billing_discount_custom,
				SUM(billing_denda) AS billing_denda,
				SUM(billing_tax) AS billing_tax,
				SUM(billing_service) AS billing_service,
				SUM(billing_other) AS billing_other,
				SUM(billing_total) AS billing_total
			FROM hot_billing
			WHERE
				user_id = '$user_id' AND
				billing_date_in LIKE '$month%' AND
				billing_status != '0' AND
				billing_status != '-1'
			GROUP BY billing_date_in
			ORDER BY billing_date_in DESC"
		)->result();

		return $billing;
	}

	public function weekly($date_start, $date_end, $user_id)
	{
		$billing = $this->db->query(
			"SELECT
				billing_date_in,
				SUM(billing_subtotal) AS billing_subtotal,
				SUM(billing_discount) AS billing_discount,
				SUM(billing_discount_custom) AS billing_discount_custom,
				SUM(billing_denda) AS billing_denda,
				SUM(billing_tax) AS billing_tax,
				SUM(billing_service) AS billing_service,
				SUM(billing_other) AS billing_other,
				SUM(billing_total) AS billing_total
			FROM hot_billing
			WHERE
				user_id = '$user_id' AND
				billing_date_in >= '$date_start' AND
				billing_date_in <= '$date_end' AND
				billing_status != '0' AND
				billing_status != '-1'
			GROUP BY billing_date_in
			ORDER BY billing_date_in DESC"
		)->result();

		return $billing;
	}

	public function daily($date,$user_id)
  {
		$billing = $this->db->query(
			"SELECT
				*
			FROM hot_billing
			WHERE
				billing_date_in = '$date' AND
				user_id = '$user_id' AND
				billing_status != '0' AND
				billing_status != '-1'
			ORDER BY billing_receipt_no ASC"
		)->result();

		return $billing;
	}

	public function range($date_start, $date_end,$user_id)
	{
		$billing = $this->db->query(
			"SELECT
				billing_date_in,
				SUM(billing_subtotal) AS billing_subtotal,
				SUM(billing_discount) AS billing_discount,
				SUM(billing_discount_custom) AS billing_discount_custom,
				SUM(billing_denda) AS billing_denda,
				SUM(billing_tax) AS billing_tax,
				SUM(billing_service) AS billing_service,
				SUM(billing_other) AS billing_other,
				SUM(billing_total) AS billing_total
			FROM hot_billing
			WHERE
				user_id = '$user_id' AND
				billing_date_in >= '$date_start' AND
				billing_date_in <= '$date_end' AND
				billing_status != '0' AND
				billing_status != '-1'
			GROUP BY billing_date_in
			ORDER BY billing_date_in DESC"
		)->result();

		return $billing;
	}

	public function detail($tx_id)
	{
		$billing = $this->db
			->join('res_payment_type', 'hot_billing.payment_type_id = res_payment_type.payment_type_id')
			->where('tx_id', $tx_id)
			->get('hot_billing')->row();
		$billing->detail = $this->db->where('tx_id', $tx_id)->get('hot_billing_detail')->result();
		$billing->buyget = $this->db
			->select('hot_billing_buyget.*,res_item.item_name,res_promo.promo_name')
			->join('res_item', 'hot_billing_buyget.get_item_id = res_item.item_id')
			->join('res_promo_buyget', 'hot_billing_buyget.promo_buyget_id = res_promo_buyget.promo_buyget_id')
			->join('res_promo', 'res_promo_buyget.promo_id = res_promo.promo_id')
			->where('tx_id', $tx_id)
			->get('hot_billing_buyget')->result();

		$billing->buyitem = $this->db
			->select('hot_billing_buyitem.*,res_promo.promo_name')
			->join('res_promo_buyitem', 'hot_billing_buyitem.promo_buyitem_id = res_promo_buyitem.promo_buyitem_id')
			->join('res_promo', 'res_promo_buyitem.promo_id = res_promo.promo_id')
			->where('tx_id', $tx_id)
			->get('hot_billing_buyitem')->result();

		$billing->buyall = $this->db
		  ->select('hot_billing_buyall.*,res_promo.promo_name')
		  ->join('res_promo_buyall', 'hot_billing_buyall.promo_buyall_id = res_promo_buyall.promo_buyall_id')
		  ->join('res_promo', 'res_promo_buyall.promo_id = res_promo.promo_id')
		  ->where('tx_id', $tx_id)
		  ->get('hot_billing_buyall')->result();

		return $billing;
	}

}
