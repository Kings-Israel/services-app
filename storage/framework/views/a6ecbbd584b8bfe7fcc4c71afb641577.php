<?php
    $isInput ??= true;
    $level ??= 0;
?>
<?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($name === '[]'): ?>
        <?php
            $description = "The request body is an array (<code>{$field['type']}</code>`)";
            $description .= !empty($field['description']) ? ", representing ".lcfirst($field['description'])."." : '.';
            if(count($field['__fields'])) $description .= " Each item has the following properties:";
        ?>
        <?php echo Parsedown::instance()->text($description); ?>


        <?php $__currentLoopData = $field['__fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subfieldName => $subfield): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(!empty($subfield['__fields'])): ?>
                    <?php if (isset($component)) { $__componentOriginal71c6471fa76ce19017edc287b6f4508c = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'scribe::components.nested-fields','data' => ['fields' => [$subfieldName => $subfield],'endpointId' => $endpointId,'isInput' => $isInput,'level' => $level + 2]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('scribe::nested-fields'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['fields' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([$subfieldName => $subfield]),'endpointId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($endpointId),'isInput' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isInput),'level' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($level + 2)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal71c6471fa76ce19017edc287b6f4508c)): ?>
<?php $component = $__componentOriginal71c6471fa76ce19017edc287b6f4508c; ?>
<?php unset($__componentOriginal71c6471fa76ce19017edc287b6f4508c); ?>
<?php endif; ?>
                <?php else: ?>
                    <div style="margin-left: <?php echo e(($level + 2) * 14); ?>px; clear: unset;">
                        <?php $__env->startComponent('scribe::components.field-details', [
                          'name' => $subfieldName,
                          'fullName' => $subfield['name'],
                          'type' => $subfield['type'] ?? 'string',
                          'required' => $subfield['required'] ?? false,
                          'description' => $subfield['description'] ?? '',
                          'example' => $subfield['example'] ?? '',
                          'endpointId' => $endpointId,
                          'hasChildren' => false,
                          'component' => 'body',
                          'isInput' => $isInput,
                        ]); ?>
                        <?php echo $__env->renderComponent(); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php elseif(!empty($field['__fields'])): ?>
        <div style="<?php if($level): ?> margin-left: <?php echo e($level * 14); ?>px;<?php else: ?> padding-left: 28px; <?php endif; ?> clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <?php $__env->startComponent('scribe::components.field-details', [
                  'name' => $name,
                  'fullName' => $field['name'],
                  'type' => $field['type'] ?? 'string',
                  'required' => $field['required'] ?? false,
                  'description' => $field['description'] ?? '',
                  'example' => $field['example'] ?? '',
                  'endpointId' => $endpointId,
                  'hasChildren' => true,
                  'component' => 'body',
                  'isInput' => $isInput,
                ]); ?>
                <?php echo $__env->renderComponent(); ?>
            </summary>
            <?php $__currentLoopData = $field['__fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subfieldName => $subfield): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(!empty($subfield['__fields'])): ?>
                    <?php if (isset($component)) { $__componentOriginal71c6471fa76ce19017edc287b6f4508c = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'scribe::components.nested-fields','data' => ['fields' => [$subfieldName => $subfield],'endpointId' => $endpointId,'isInput' => $isInput,'level' => $level + 1]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('scribe::nested-fields'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['fields' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([$subfieldName => $subfield]),'endpointId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($endpointId),'isInput' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isInput),'level' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($level + 1)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal71c6471fa76ce19017edc287b6f4508c)): ?>
<?php $component = $__componentOriginal71c6471fa76ce19017edc287b6f4508c; ?>
<?php unset($__componentOriginal71c6471fa76ce19017edc287b6f4508c); ?>
<?php endif; ?>
                <?php else: ?>
                    <div style="margin-left: <?php echo e(($level + 1) * 14); ?>px; clear: unset;">
                        <?php $__env->startComponent('scribe::components.field-details', [
                          'name' => $subfieldName,
                          'fullName' => $subfield['name'],
                          'type' => $subfield['type'] ?? 'string',
                          'required' => $subfield['required'] ?? false,
                          'description' => $subfield['description'] ?? '',
                          'example' => $subfield['example'] ?? '',
                          'endpointId' => $endpointId,
                          'hasChildren' => false,
                          'component' => 'body',
                          'isInput' => $isInput,
                        ]); ?>
                        <?php echo $__env->renderComponent(); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </details>
        </div>
    <?php else: ?>
        <div style="<?php if($level): ?> margin-left: <?php echo e(($level + 1) * 14); ?>px;<?php else: ?> padding-left: 28px; <?php endif; ?> clear: unset;">
            <?php $__env->startComponent('scribe::components.field-details', [
              'name' => $name,
              'fullName' => $field['name'],
              'type' => $field['type'] ?? 'string',
              'required' => $field['required'] ?? false,
              'description' => $field['description'] ?? '',
              'example' => $field['example'] ?? '',
              'endpointId' => $endpointId,
              'hasChildren' => false,
              'component' => 'body',
              'isInput' => $isInput,
            ]); ?>
            <?php echo $__env->renderComponent(); ?>
        </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\xampp\htdocs\projects\laravel\services-app\vendor\knuckleswtf\scribe\src/../resources/views//components/nested-fields.blade.php ENDPATH**/ ?>