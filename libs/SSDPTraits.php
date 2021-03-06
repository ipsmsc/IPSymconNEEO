<?


/**
 * Trait mit Hilfsfunktionen für den Datenaustausch.
 * @property integer $ParentID
 */
trait InstanceStatus
{
    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    protected function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        switch ($Message) {
            case FM_CONNECT:
                $this->RegisterParent();
                if ($this->HasActiveParent())
                    $this->IOChangeState(IS_ACTIVE);
                else
                    $this->IOChangeState(IS_INACTIVE);
                break;
            case FM_DISCONNECT:
                $this->RegisterParent();
                $this->IOChangeState(IS_INACTIVE);
                break;
            case IM_CHANGESETTINGS:
                $this->SendDebug('SETTINGS', $Data, 0);
                if ($SenderID == $this->ParentID) {
                    $this->RegisterParent();
                }
                break;
            case IM_CHANGESTATUS:
                if ($SenderID == $this->ParentID) {
                    $this->IOChangeState($Data[0]);
                }

                break;
        }
    }

    /**
     * Ermittelt den Parent und verwaltet die Einträge des Parent im MessageSink
     * Ermöglicht es das Statusänderungen des Parent empfangen werden können.
     * 
     * @access protected
     * @return int ID des Parent.
     */
    protected function RegisterParent()
    {
        $OldParentId = $this->ParentID;
        $ParentId = @IPS_GetInstance($this->InstanceID)['ConnectionID'];
        if ($ParentId <> $OldParentId) {
            if ($OldParentId > 0) {
                $this->UnregisterMessage($OldParentId, IM_CHANGESTATUS);
                $this->UnregisterMessage($OldParentId, IM_CHANGESETTINGS);
            }
            if ($ParentId > 0) {
                $this->RegisterMessage($ParentId, IM_CHANGESTATUS);
                $this->RegisterMessage($ParentId, IM_CHANGESETTINGS);
            } else {
                $ParentId = 0;
            }
            $this->ParentID = $ParentId;
        }
        return $ParentId;
    }

    /**
     * Prüft den Parent auf vorhandensein und Status.
     * 
     * @access protected
     * @return bool True wenn Parent vorhanden und in Status 102, sonst false.
     */
    protected function HasActiveParent()
    {
        $instance = IPS_GetInstance($this->InstanceID);
        if ($instance['ConnectionID'] > 0) {
            $parent = IPS_GetInstance($instance['ConnectionID']);
            if ($parent['InstanceStatus'] == 102)
                return true;
        }
        return false;
    }

}


/** @} */