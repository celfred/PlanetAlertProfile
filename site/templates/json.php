function pagesToJSON(PageArray $items) {
  $a = array();
  foreach($items as $item) {
    $a[] = pageToArray($item); 
  }
  return json_encode($a); 
}

function pageToArray(Page $page) {

  $outputFormatting = $page->outputFormatting;
  $page->setOutputFormatting(false);

  $data = array(
    'id' => $page->id,
    'parent_id' => $page->parent_id,
    'templates_id' => $page->templates_id,
    'name' => $page->name,
    'status' => $page->status,
    'sort' => $page->sort,
    'sortfield' => $page->sortfield,
    'numChildren' => $page->numChildren,
    'template' => $page->template->name,
    'parent' => $page->parent->path,
    'data' => array(),
    );

  foreach($page->template->fieldgroup as $field) {
    if($field->type instanceof FieldtypeFieldsetOpen) continue;
    $value = $page->get($field->name); 
    $data['data'][$field->name] = $field->type->sleepValue($page, $field, $value);
  }

  $page->setOutputFormatting($outputFormatting);

  return $data;
}
