<?php

class Model_Type_DevelopmentStatus extends Model_Type_Enum
{
    protected $name = 'developmentstatus';
    protected $values = array('supported', 'under development', 'planned', 'deprecated', 'partially deprecated');

}
