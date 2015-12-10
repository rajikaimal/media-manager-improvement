<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla CMS.
 * Provides a modal media selector including upload mechanism
 *
 * @since  1.6
 */
class JFormFieldMedia extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'Media';

	/**
	 * The authorField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $authorField;

	/**
	 * The asset.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $asset;

	/**
	 * The link.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $link;

	/**
	 * Mode to restrict upload process.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $mode;

	/**
	 * Modal width.
	 *
	 * @var    integer
	 * @since  3.4.5
	 */
	protected $width;

	/**
	 * Modal height.
	 *
	 * @var    integer
	 * @since  3.4.5
	 */
	protected $height;

	/**
	 * The authorField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $preview;

	/**
	 * The preview.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $directory;

	/**
	 * The component for image path retrieving.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $component;

	/**
	 * The previewWidth.
	 *
	 * @var    int
	 * @since  3.2
	 */
	protected $previewWidth;

	/**
	 * The previewHeight.
	 *
	 * @var    int
	 * @since  3.2
	 */
	protected $previewHeight;

	/**
	 * Layout to render
	 *
	 * @var  string
	 */
	protected $layout = 'joomla.form.field.media';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'authorField':
			case 'asset':
			case 'link':
			case 'mode':
			case 'width':
			case 'height':
			case 'preview':
			case 'directory':
			case 'component':
			case 'previewWidth':
			case 'previewHeight':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'authorField':
			case 'asset':
			case 'link':
			case 'mode':
			case 'width':
			case 'height':
			case 'preview':
			case 'directory':
			case 'component':
				$this->$name = (string) $value;
				break;

			case 'previewWidth':
			case 'previewHeight':
				$this->$name = (int) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see 	JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		if ($result == true)
		{
			$assetField = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';

			$this->authorField   = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
			$this->asset         = $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string) $this->element['asset_id'];
			$this->link          = (string) $this->element['link'];
			$this->width  	     = isset($this->element['width']) ? (int) $this->element['width'] : 800;
			$this->height 	     = isset($this->element['height']) ? (int) $this->element['height'] : 500;
			$this->mode          = isset($this->element['mode']) ? (string) $this->element['mode'] : 'normal';
			$this->preview       = (string) $this->element['preview'];
			$this->directory     = (string) $this->element['directory'];
			$this->component     = (isset($this->element['component']) && JComponentHelper::isInstalled($this->element['component'])) ? (string) $this->element['component'] : 'com_media';
			$this->previewWidth  = isset($this->element['preview_width']) ? (int) $this->element['preview_width'] : 200;
			$this->previewHeight = isset($this->element['preview_height']) ? (int) $this->element['preview_height'] : 200;
		}

		return $result;
	}

	/**
	 * Method to get the field input markup for a media selector.
	 * Use attributes to identify specific created_by and asset_id fields
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		if (empty($this->layout))
		{
			throw new UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
		}

		if (!JFactory::getUser()->authorise('core.create', 'com_media') && $this->mode == 'strict')
		{
			return '<div class="field-media-wrapper">' . JText::_('JLIB_MEDIA_ERROR_WARNNOUPLOADRIGHTS') . '</div>';
		}

		return $this->getRenderer($this->layout)->render($this->getLayoutData());
	}

	/**
	 * Get the data that is going to be passed to the layout
	 *
	 * @return  array
	 */
	public function getLayoutData()
	{
		// Get the basic field data
		$data = parent::getLayoutData();

		$asset = $this->asset;

		if ($asset == '')
		{
			$asset = JFactory::getApplication()->input->get('option');
		}

		$this->folder = $this->translatePath($this->directory);

		if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value))
		{
			$this->folder = explode('/', $this->value);
			$this->folder = array_diff_assoc($this->folder, explode('/', JComponentHelper::getParams($this->component)->get('image_path', 'images')));
			array_pop($this->folder);
			$this->folder = implode('/', $this->folder);
		}

		$extraData = array(
				'asset'         => $asset,
				'authorField'   => $this->authorField,
				'authorId'      => $this->form->getValue($this->authorField),
				'folder'        => $this->folder,
				'link'          => $this->link,
				'preview'       => $this->preview,
				'previewHeight' => $this->previewHeight,
				'previewWidth'  => $this->previewWidth,
		);

		return array_merge($data, $extraData);
	}

	/**
	 * Translates directory value to correct path
	 *
	 * Possible parameters:
	 * {username}, {userid}
	 * Format characters of date() - Example: {d}, {m} or {Y}
	 *
	 * @param  string  $directory  Upload path which was specified in the manifest file
	 *
	 * @return  string
	 *
	 * @since   3.5
	 */
	protected function translatePath($directory)
	{
		// First set the directory as path
		$path = $directory;

		if (preg_match_all('@{(.*)}@U', $directory, $matches))
		{
			foreach ($matches[1] as &$match)
			{
				switch ($match)
				{
					case 'userid':
						$match = JFactory::getUser()->id;
						break;

					case 'username':
						$match = JFactory::getUser()->username;
						break;

					default:
						$match = date($match);
				}
			}

			$path = str_replace($matches[0], $matches[1], $directory);
		}

		return $path;
	}
}
