<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$user = Auth::requireLogin();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    try {
        Csrf::verify();
        $courseId = Input::intRange($_POST['course_id'] ?? null, 1, 1000000, 'course_id');

        $course = Database::run('SELECT * FROM courses WHERE id = ?', [$courseId])->fetch();
        if (!$course) {
            throw new ValidationError(['course_id' => 'Course does not exist.']);
        }
        $count = (int) Database::run(
            'SELECT COUNT(*) FROM enrolments WHERE course_id = ? AND status = "enrolled"',
            [$courseId]
        )->fetchColumn();
        if ($count >= (int) $course['capacity']) {
            throw new ValidationError(['course_id' => 'Course has reached full capacity.']);
        }
        Database::run(
            'INSERT INTO enrolments (user_id, course_id, status) VALUES (?, ?, "pending")',
            [$user['id'], $courseId]
        );
        $success = 'Registration request submitted for ' . $course['code'] . '.';
        Logger::log('course_registration', ['outcome' => 'pending', 'course_id' => (string) $courseId]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $errors['course_id'] = 'You already registered this course.';
        } else {
            throw $e;
        }
    } catch (ValidationError $e) {
        $errors = $e->errors;
    }
}

// Course list with each student's existing registration status.
$courses = Database::run(
    'SELECT c.*,
            (SELECT COUNT(*) FROM enrolments e
              WHERE e.course_id = c.id AND e.status = "enrolled") AS enrolled_count,
            (SELECT status FROM enrolments e
              WHERE e.course_id = c.id AND e.user_id = ?) AS my_status
     FROM courses c ORDER BY c.code',
    [$user['id']]
)->fetchAll();

$pageTitle = 'Register Courses';
require __DIR__ . '/../views/header.php';
?>
<div class="card">
  <h1>Course Registration</h1>
  <p class="muted">Select courses to register for. Capacity and your current
  registration status are shown.</p>

  <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
  <?php if (isset($errors['csrf_token'])): ?><div class="alert alert-error"><?= e($errors['csrf_token']) ?></div><?php endif; ?>
  <?php if (!empty($errors['course_id']) && !isset($errors['csrf_token'])): ?>
    <div class="alert alert-error"><?= e($errors['course_id']) ?></div>
  <?php endif; ?>

  <table>
    <tr><th>Code</th><th>Title</th><th>Units</th><th>Capacity</th><th>Status</th><th></th></tr>
    <?php foreach ($courses as $course): ?>
    <tr>
      <td><?= e($course['code']) ?></td>
      <td><?= e($course['title']) ?></td>
      <td><?= (int) $course['credit_units'] ?></td>
      <td><?= (int) $course['enrolled_count'] ?> / <?= (int) $course['capacity'] ?></td>
      <td><?= $course['my_status'] ? e($course['my_status']) : '<span class="muted">not registered</span>' ?></td>
      <td>
        <?php if (!$course['my_status']): ?>
        <form method="post" action="/courses.php">
          <?= Csrf::hiddenField() ?>
          <input type="hidden" name="action" value="register">
          <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
          <button type="submit">Register</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php require __DIR__ . '/../views/footer.php'; ?>
