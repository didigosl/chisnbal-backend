<?php

namespace Common\Models;

class SAdminLoginLog extends Model {

	/**
	 *
	 * @var integer
	 */
	public $id;

	/**
	 *
	 * @var string
	 */
	public $username;

	/**
	 *
	 * @var integer
	 */
	public $admin_id;

	/**
	 *
	 * @var string
	 */
	public $ip;

	/**
	 *
	 * @var string
	 */
	public $result;

	/**
	 *
	 * @var integer
	 */
	public $reason;

	/**
	 *
	 * @var integer
	 */
	public $create_time;

	static public function getPkCol(){
        return 'id';
    }

	/**
	 * Returns table name mapped in the model.
	 *
	 * @return string
	 */
	public function getSource() {
		return 's_admin_login_log';
	}

	public function initialize() {
		$this->useDynamicUpdate(true);
		$this->skipAttributesOnUpdate(array('create_time'));
	}
}
