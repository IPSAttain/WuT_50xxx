<?php
	class WuT50xxx extends IPSModule {

		public function Create()
		{
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyString("IPAddress", "192.168.1.1");
			$this->RegisterPropertyInteger("Port", 49153);
			$this->RegisterTimer("TimeOut", 600000, "WuT_Initialize($this->InstanceID);");
			$this->RegisterMessage(0, IPS_KERNELSTARTED);
			for ($i=0; $i<=11; $i++) {
				$Ident = "WuT_Output_" . str_pad($i, 2, "0", STR_PAD_LEFT);
				$this->RegisterVariableBoolean($Ident, "Output_" . str_pad($i, 2, "0", STR_PAD_LEFT), '~Switch', 0);
				$this->EnableAction($Ident);
			}
		}

		public function Destroy()
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();

			$this->ForceParent("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}");
			$this->GetConfigurationForParent();
			$this->Initialize();
			$this->RegisterParent();
		}

		public function GetConfigurationForParent() {
			$ipaddress = $this->ReadPropertyString("IPAddress");
			$port = $this->ReadPropertyInteger("Port");
			return "{\"Host\": \"$ipaddress\", \"Port\": $port}";
			//return "{\"Port\": $port}";
		}

		protected function RegisterParent()
		{
			$OldParentId = $this->GetBuffer('ParentID');
			$ParentId = @IPS_GetInstance($this->InstanceID)['ConnectionID'];
			if ($ParentId <> $OldParentId)
			{
				if ($OldParentId > 0)
					$this->UnregisterMessage($OldParentId, IM_CHANGESTATUS);
				if ($ParentId > 0)
					$this->RegisterMessage($ParentId, IM_CHANGESTATUS); // enable notification for parent client socket
				else
					$ParentId = 0;
				$this->SetBuffer('ParentID',$ParentId);
				$this->SendDebug(__FUNCTION__, $ParentId, 0);
			}
			return $ParentId;
		}  

		public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
		{
			switch ($Message)
			{
				case IPS_KERNELSTARTED:
					$this->RegisterParent();
					$this->Initialize();
					break;
				case IM_CHANGESTATUS:
					if ($SenderID == $this->GetBuffer("ParentID"))
					{
						$this->SendDebug(__FUNCTION__, $Data[0], 0);
						if ($Data[0] == 102)  // if parent client socket becomes ready
						{
							$this->Initialize();
						}
					}
					break;
			}
		}

		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);
			$this->SendDebug(__FUNCTION__, utf8_decode($data->Buffer) , 1);
			$Payload = utf8_decode($data->Buffer);
			if (ord($Payload[4]) == 8){   		              				// 8 =  data from input register
				$Integer =  ord($Payload[10])+ ord($Payload[11])*256 ;    //  10 -> low Byte | 11 -> high Byte
				for ($i=0; $i<=11; $i++) {
					$Ident = "WuT_Input_" . str_pad($i, 2, "0", STR_PAD_LEFT);
					$Bit = (($Integer >> $i) & 1) == 1;
					$this->RegisterVariableBoolean($Ident, "Input_" . str_pad($i, 2, "0", STR_PAD_LEFT), '~Switch', 0);
					if($this->GetValue($Ident) != $Bit){
						$this->SetValue($Ident, $Bit);
					}
				}
			}
			else
			{
				$this->SendDebug(__FUNCTION__, 'Unknown Payload' . ord($Payload[4]) , 1);	
			}
		}

		public function RequestAction($Ident, $Value){
			$BitNo = (int) substr($Ident, -2);
			$Payload = "\x00\x00\x00\x00\x09\x00\x0c\x00";
			$Int = pow(2,$BitNo);
			$Payload .= chr(1 << $BitNo) . chr($Int >> 8);
			if ($Value) $Payload .= "\xff\x0f";
			else $Payload .= "\x00\x00";
			$this->SetValue($Ident, $Value);
			$this->Send($Payload);
		}

		public function Initialize()
		{
			if ($this->HasActiveParent()){
				$Payload = "\x00\x00\x00\x00\x08\x00\x0c\x00\x01\x00";  // send output state to the I/O device
				$Int = 0;
				for ($i=0; $i<=7; $i++) {
					$Ident = "WuT_Output_" . str_pad($i, 2, "0", STR_PAD_LEFT);
					$Bit = $this->GetValue($Ident);
					if ($Bit) $Int += pow(2,$i);
				}
				$Payload .= chr($Int);
				$Int = 0;
				for ($i=8; $i<=11; $i++) {
					$Ident = "WuT_Output_" . str_pad($i, 2, "0", STR_PAD_LEFT);
					$Bit = $this->GetValue($Ident);
					if ($Bit) $Int += pow(2,$i-8);
				}
				$Payload .= chr($Int);
				$this->Send($Payload);
				sleep(1);
				$Payload = "\x00\x00\x00\x00\x10\x00\x0c\x00\xff\x0f"; // \xff\xf0 = enable async notification for all inputs
				$Payload .= "\xb8\x0b"; // b8 0b = additional cyclic notification every 300s
				$this->Send($Payload);
				sleep(1);
				$Payload = "\x00\x00\x00\x00\x01\x00\x08\x00"; // request input state
				$this->Send($Payload);
			}
			else
			{
				$this->SendDebug(__FUNCTION__, 'Could not execute' . __FUNCTION__ . '. No active parent', 0);
			}
		}
		
		protected function Send(string $Payload)
		{
			$this->SendDebug(__FUNCTION__, 'Send to parent:' . $Payload , 1);
			$Payload = utf8_encode($Payload);
			$this->SendDataToParent(@json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => $Payload)));
		}
	}