<?php

define ('MANDATORY_ATTR_ID', 3); # FQDN
define ('MANDATORY_FOR_LISTSRC', '{requires FQDN}'); # RackCode

registerOpHandler ('object', 'edit', 'update', 'requireMandatoryAttrWithValue');
registerOpHandler ('object', 'edit', 'clearSticker', 'requireMandatoryAttrWithoutValue');

function requireMandatoryAttrWithValue()
{
	return requireMandatoryAttrGeneric (MANDATORY_FOR_LISTSRC, MANDATORY_ATTR_ID, getAttrNewValue (MANDATORY_ATTR_ID));
}

function requireMandatoryAttrWithoutValue()
{
	return requireMandatoryAttrGeneric (MANDATORY_FOR_LISTSRC, MANDATORY_ATTR_ID, NULL);
}

function getAttrNewValue ($attr_id)
{
	$num_attrs = genericAssertion ('num_attrs', 'uint0');
	for ($i = 0; $i < $num_attrs; $i++)
		if ($attr_id == genericAssertion ("${i}_attr_id", 'uint'))
			return genericAssertion ("${i}_value", 'string0');
	return NULL;
}

function requireMandatoryAttrGeneric ($listsrc, $attr_id, $newval)
{
	$object_id = getBypassValue();
	$attrs = getAttrValues ($object_id);
	if
	(
		array_key_exists ($attr_id, $attrs) &&
		considerGivenConstraint (spotEntity ('object', $object_id), $listsrc) &&
		! mb_strlen ($newval)
	)
	{
		showError ('Mandatory attribute "' . $attrs[$attr_id]['name'] . '" not set');
		stopOpPropagation();
	}
	return '';
}
