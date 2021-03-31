<?php


require_once('webhook_endpoint/ApaleoConnector.php');
require_once('webhook_endpoint/ApaleoManager.php');
require_once('leiwandlock/LeiwandLockManager.php');

/**
 * Class ReservationManager
 * @copyright Stefan Wölflinger padalton86@gmail.com
 */
class ReservationManager extends ApaleoManager
{

    public function created()
    {
        // no action needed
    }

    public function changed()
    {
        global $db;
        $reservation = $this->connector->callMethod("booking/v1/reservations/" . $this->webhook_data->data->entityId, "GET", array("expand" => "timeSlices,services,booker"));
        if (!empty($reservation->unit->id) && $reservation->status == 'InHouse') {
            $res = $db->query("SELECT * FROM leiwand_units WHERE room_id = '{$reservation->unit->id}' AND active = '1' AND synced = '1' AND property_id = '" . $reservation->property->id . "' AND account_id = '".$_SESSION['account_code']."'");
            $row = $res->fetch();
            if (!empty($row['id'])) {
                $ll_manager = new LeiwandLockManager($reservation);
                $ll_manager->update();
            }
        }
    }

    public function amended()
    {
        // no action needed
    }

    public function checked_in()
    {
        global $db;
        $reservation = $this->connector->callMethod("booking/v1/reservations/" . $this->webhook_data->data->entityId, "GET", array("expand" => "timeSlices,services,booker"));
        if (!empty($reservation->unit->id)) {
            $res = $db->query("SELECT * FROM leiwand_units WHERE room_id = '{$reservation->unit->id}' AND active = '1'  AND synced = '1' AND property_id = '" . $reservation->property->id . "' AND account_id = '".$_SESSION['account_code']."'");
            $row = $res->fetch();
            if (!empty($row['id'])) {
                $ll_manager = new LeiwandLockManager($reservation);
                $ll_manager->create();
            }
        }
    }

    public function checked_out()
    {
        // no action needed
    }

    public function set_to_no_show()
    {
        $reservation = $this->connector->callMethod("booking/v1/reservations/" . $this->webhook_data->data->entityId, "GET", array("expand" => "timeSlices,services,booker"));
        if (!empty($reservation->unit->id)) {
            global $db;
            $res = $db->query("SELECT * FROM leiwand_units WHERE room_id = '{$reservation->unit->id}' AND active = '1' AND synced = '1' AND property_id = '" . $reservation->property->id . "' AND account_id = '".$_SESSION['account_code']."'");
            $row = $res->fetch();
            if (!empty($row['id'])) {
                $ll_manager = new LeiwandLockManager($reservation);
                $ll_manager->update();
            }
        }
    }

    public function unit_assigned()
    {
        $reservation = $this->connector->callMethod("booking/v1/reservations/" . $this->webhook_data->data->entityId, "GET", array("expand" => "timeSlices,services,booker"));
        if (!empty($reservation->unit->id)) {
            global $db;
            $res = $db->query("SELECT * FROM leiwand_units WHERE room_id = '{$reservation->unit->id}' AND active = '1' AND synced = '1' AND property_id = '" . $reservation->property->id . "' AND account_id = '".$_SESSION['account_code']."'");
            $row = $res->fetch();
            if (!empty($row['id'])) {
                $ll_manager = new LeiwandLockManager($reservation);
                $ll_manager->update();
            }
        }
    }

    public function unit_unassigned()
    {
        // no action needed
    }

    public function canceled()
    {
        $reservation = $this->connector->callMethod("booking/v1/reservations/" . $this->webhook_data->data->entityId, "GET", array("expand" => "timeSlices,services,booker"));
        if (!empty($reservation->unit->id)) {
            global $db;
            $res = $db->query("SELECT * FROM leiwand_units WHERE room_id = '{$reservation->unit->id}' AND active = '1' AND synced = '1' AND property_id = '" . $reservation->property->id . "' AND account_id = '".$_SESSION['account_code']."'");
            $row = $res->fetch();
            if (!empty($row['id'])) {
                $ll_manager = new LeiwandLockManager($reservation);
                $ll_manager->update();
            }
        }
    }

}