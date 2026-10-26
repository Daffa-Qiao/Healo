# Class Diagram

Diagram ini merepresentasikan class/model yang benar-benar ada pada aplikasi. Tabel yang hanya muncul sebagai rencana pada ERD lama (`foods`, `food_scans`, `food_logs`, `menus`, `ai_usage_logs`, dan sejenisnya) tidak dimasukkan karena tidak memiliki model/migration aktif saat analisis ini dibuat.

Notasi:

- `[enc]` = cast terenkripsi saat disimpan.
- `[derived]` = nilai dihitung/sinkronkan server dari source record.
- Kardinalitas memakai bentuk `1`, `0..1`, dan `0..*`.

## CD-01 — Peta model domain keseluruhan

```mermaid
classDiagram
    class User
    class HealthProfile
    class BmiRecord
    class HealthJournal
    class HealthJournalEntry
    class NutritionProfile
    class Meal
    class MealItem
    class NutritionDailySummary
    class FoodAnalysis
    class HealthyMenu
    class HealthConversation
    class HealthMessage
    class SymptomAssessment
    class HealthGoal
    class HealthPlan
    class HealthPlanTask
    class UserNotification
    class NotificationPreference
    class AuditLog

    User "1" --> "0..1" HealthProfile : has
    User "1" --> "0..*" BmiRecord : records
    User "1" --> "0..*" HealthJournal : owns
    HealthJournal "1" *-- "0..*" HealthJournalEntry : contains
    User "1" --> "0..1" NutritionProfile : configures
    User "1" --> "0..*" Meal : logs
    Meal "1" *-- "0..*" MealItem : contains
    User "1" --> "0..*" NutritionDailySummary : summarizes
    User "1" --> "0..*" FoodAnalysis : reviews
    User "1" --> "0..*" HealthConversation : owns
    HealthConversation "1" *-- "0..*" HealthMessage : contains
    User "1" --> "0..*" SymptomAssessment : owns
    HealthConversation "0..1" --> "0..*" SymptomAssessment : relates
    HealthJournal "0..1" --> "0..*" SymptomAssessment : receives
    User "1" --> "0..*" HealthGoal : owns
    User "1" --> "0..*" HealthPlan : owns
    HealthPlan "1" *-- "0..*" HealthPlanTask : contains
    User "1" --> "0..*" UserNotification : receives
    User "1" --> "0..1" NotificationPreference : configures
    User "0..1" --> "0..*" AuditLog : acts
```

## CD-02 — Core account, health profile, BMI, dan jurnal

```mermaid
classDiagram
    class User {
        +bigint id
        +string name
        +string email
        +UserRole role
        +string locale
        +datetime email_verified_at
        +datetime deleted_at
        +isAdmin() bool
        +healthProfile() HasOne
        +bmiRecords() HasMany
        +healthJournals() HasMany
        +healthJournalEntries() HasManyThrough
    }

    class HealthProfile {
        +bigint id
        +bigint user_id
        +decimal height_cm
        +decimal weight_kg
        +date birth_date
        +Gender gender
        +ActivityLevel activity_level
        +HealthGoal health_goal
        +array health_conditions [enc]
        +array allergies [enc]
        +datetime consented_at
        +user() BelongsTo
    }

    class BmiRecord {
        +bigint id
        +bigint user_id
        +decimal height_cm
        +decimal weight_kg
        +decimal bmi [derived]
        +BmiCategory category [derived]
        +datetime measured_at
        +user() BelongsTo
    }

    class HealthJournal {
        +bigint id
        +bigint user_id
        +string title
        +date journal_date
        +Mood mood
        +decimal weight_kg
        +int systolic
        +int diastolic
        +decimal sleep_hours
        +decimal water_liters
        +int exercise_minutes
        +datetime deleted_at
        +user() BelongsTo
        +entries() HasMany
        +entryContent(type) string
        +symptomList() array
    }

    class HealthJournalEntry {
        +bigint id
        +bigint health_journal_id
        +JournalEntryType type
        +string content [enc]
        +int sort_order
        +journal() BelongsTo
    }

    class AuditLog {
        +bigint id
        +bigint actor_id
        +string action
        +string subject_type
        +bigint subject_id
        +array safe_metadata
        +string ip_hash
        +string user_agent
        +datetime created_at
        +actor() BelongsTo
        +subject() MorphTo
    }

    User "1" --> "0..1" HealthProfile
    User "1" --> "0..*" BmiRecord
    User "1" --> "0..*" HealthJournal
    HealthJournal "1" *-- "0..*" HealthJournalEntry
    User "0..1" --> "0..*" AuditLog : actor
    AuditLog ..> HealthProfile : subject
    AuditLog ..> BmiRecord : subject
    AuditLog ..> HealthJournal : subject
```

## CD-03 — Nutrisi dan analisis makanan

```mermaid
classDiagram
    class User {
        +nutritionProfile() HasOne
        +meals() HasMany
        +mealItems() HasManyThrough
        +nutritionDailySummaries() HasMany
        +foodAnalyses() HasMany
    }

    class NutritionProfile {
        +bigint id
        +bigint user_id
        +int daily_calorie_target
        +ActivityLevel activity_level
        +NutritionGoal goal
        +DietPreference diet_preference
        +array food_preferences [enc]
        +string target_source
        +datetime calculated_at
        +user() BelongsTo
    }

    class Meal {
        +bigint id
        +bigint user_id
        +date meal_date
        +MealType meal_type
        +string notes [enc]
        +decimal total_calories [derived]
        +datetime deleted_at
        +user() BelongsTo
        +items() HasMany
    }

    class MealItem {
        +bigint id
        +bigint meal_id
        +string food_name
        +decimal serving_size
        +string serving_unit
        +decimal calories
        +decimal protein_g
        +decimal carbohydrates_g
        +decimal fat_g
        +NutritionSource source
        +decimal confidence
        +meal() BelongsTo
    }

    class NutritionDailySummary {
        +bigint id
        +bigint user_id
        +date summary_date
        +int calorie_target [derived]
        +decimal calories_consumed [derived]
        +decimal calories_remaining [derived]
        +int meals_logged [derived]
        +user() BelongsTo
    }

    class FoodAnalysis {
        +bigint id
        +bigint user_id
        +string image_path [enc]
        +FoodAnalysisStatus status
        +array result [enc]
        +decimal confidence
        +string provider
        +string failure_code [enc]
        +datetime analyzed_at
        +datetime confirmed_at
        +datetime expires_at
        +user() BelongsTo
    }

    class HealthyMenu {
        +bigint id
        +string name
        +string slug
        +MealType meal_type
        +DietPreference diet_preference
        +decimal calories
        +decimal protein_g
        +decimal carbohydrates_g
        +decimal fat_g
        +array ingredients
        +string preparation
        +string source
        +string attribution
        +bool active
    }

    User "1" --> "0..1" NutritionProfile
    User "1" --> "0..*" Meal
    Meal "1" *-- "0..*" MealItem
    User "1" --> "0..*" NutritionDailySummary
    User "1" --> "0..*" FoodAnalysis
    FoodAnalysis ..> Meal : confirmation creates
    NutritionProfile ..> HealthyMenu : filters recommendations
```

## CD-04 — AI health, assessment, goal, dan health plan

```mermaid
classDiagram
    class User {
        +healthConversations() HasMany
        +symptomAssessments() HasMany
        +healthGoals() HasMany
        +healthPlans() HasMany
    }

    class HealthConversation {
        +bigint id
        +bigint user_id
        +string title [enc]
        +datetime last_message_at
        +user() BelongsTo
        +messages() HasMany
        +assessments() HasMany
    }

    class HealthMessage {
        +bigint id
        +bigint health_conversation_id
        +HealthMessageRole role
        +HealthMessageType message_type
        +string content [enc]
        +array metadata [enc]
        +conversation() BelongsTo
    }

    class SymptomAssessment {
        +bigint id
        +bigint user_id
        +bigint health_conversation_id
        +bigint health_journal_id
        +string input [enc]
        +string summary [enc]
        +SymptomUrgency urgency
        +array red_flags [enc]
        +array symptoms [enc]
        +array possible_categories [enc]
        +string recommendation [enc]
        +array differential [enc]
        +string recommended_specialty [enc]
        +bool referral_needed
        +AIConfidence confidence
        +string provider
        +string model
        +datetime saved_to_journal_at
        +user() BelongsTo
        +conversation() BelongsTo
        +journal() BelongsTo
    }

    class HealthGoal {
        +bigint id
        +bigint user_id
        +HealthGoalType type
        +decimal target_value
        +decimal current_value [derived]
        +string unit
        +date start_date
        +date target_date
        +HealthGoalStatus status
        +array metadata [enc]
        +user() BelongsTo
    }

    class HealthPlan {
        +bigint id
        +bigint user_id
        +date plan_date
        +HealthPlanStatus status [derived]
        +int progress_percentage [derived]
        +datetime generated_at
        +datetime completed_at
        +user() BelongsTo
        +tasks() HasMany
    }

    class HealthPlanTask {
        +bigint id
        +bigint health_plan_id
        +HealthPlanTaskType type
        +string title
        +string description
        +HealthPlanTaskStatus status
        +int priority
        +HealthPlanTaskSource source
        +string source_reference
        +HealthPlanTaskCompletionMode completion_mode
        +datetime completed_at
        +array metadata [enc]
        +healthPlan() BelongsTo
    }

    class HealthJournal
    class Meal
    class BmiRecord
    class NutritionProfile

    User "1" --> "0..*" HealthConversation
    HealthConversation "1" *-- "0..*" HealthMessage
    User "1" --> "0..*" SymptomAssessment
    HealthConversation "0..1" --> "0..*" SymptomAssessment
    HealthJournal "0..1" --> "0..*" SymptomAssessment
    User "1" --> "0..*" HealthGoal
    User "1" --> "0..*" HealthPlan
    HealthPlan "1" *-- "0..*" HealthPlanTask
    BmiRecord ..> HealthGoal : progress source
    Meal ..> HealthGoal : progress source
    HealthJournal ..> HealthGoal : progress source
    NutritionProfile ..> HealthPlan : generation input
    HealthGoal ..> HealthPlan : generation input
    Meal ..> HealthPlanTask : source-derived completion
    HealthJournal ..> HealthPlanTask : source-derived completion
```

## CD-05 — Notifikasi dan audit operasional

```mermaid
classDiagram
    class User {
        +userNotifications() HasMany
        +notificationPreference() HasOne
    }

    class UserNotification {
        +bigint id
        +bigint user_id
        +NotificationType type
        +string title
        +string body
        +array data
        +string action_url
        +string deduplication_key
        +date reference_date
        +datetime available_at
        +datetime delivered_at
        +datetime read_at
        +user() BelongsTo
        +scopeUnread(query) Builder
    }

    class NotificationPreference {
        +bigint id
        +bigint user_id
        +bool notifications_enabled
        +bool health_plan_enabled
        +bool meal_reminder_enabled
        +bool journal_reminder_enabled
        +bool health_goal_enabled
        +bool health_insight_enabled
        +bool wellness_enabled
        +bool system_enabled
        +time quiet_hours_start
        +time quiet_hours_end
        +time reminder_time
        +user() BelongsTo
    }

    class AuditLog {
        +bigint id
        +bigint actor_id
        +string action
        +string subject_type
        +bigint subject_id
        +array safe_metadata
        +string ip_hash
        +string user_agent
        +datetime created_at
        +actor() BelongsTo
        +subject() MorphTo
    }

    class NotificationCreated {
        +UserNotification notification
        +broadcastOn() PrivateChannel
        +broadcastWith() array
    }

    class SendWellnessReminderJob {
        +bigint userId
        +string referenceDate
        +int tries
        +array backoff
        +handle(reminderService) void
    }

    User "1" --> "0..*" UserNotification
    User "1" --> "0..1" NotificationPreference
    User "0..1" --> "0..*" AuditLog : actor
    UserNotification --> NotificationCreated : broadcasts
    SendWellnessReminderJob --> User : loads recipient
    SendWellnessReminderJob ..> UserNotification : generates through service
    NotificationPreference ..> UserNotification : controls eligibility/timing
```

## CD-06 — Service layer dan dependensi utama

```mermaid
classDiagram
    class BmiRecordService {
        +record(user, data) BmiRecord
    }
    class BmiCalculator {
        +calculate(height, weight) BmiResult
    }
    class HealthJournalService {
        +create(user, data) HealthJournal
        +update(journal, data) HealthJournal
    }
    class MealService
    class MealItemService
    class NutritionSummaryService
    class FoodAnalysisService {
        +analyze(user, image) FoodAnalysis
        +confirm(analysis, data) Meal
        +cancel(analysis) void
    }
    class FoodAnalysisProviderInterface {
        <<interface>>
        +analyze(path, originalName) FoodAnalysisResult
    }
    class HealthAssistantService
    class SymptomAssessmentService {
        +assess(user, input, save, conversation) Result
    }
    class HealthAIProviderInterface {
        <<interface>>
    }
    class EmergencyDetectionService {
        +detect(input) array
    }
    class MedicalSafetyService
    class HealthInsightsService {
        +overview(user, period) array
        +trends(user, period) array
        +weekly(user) array
        +personalized(user, period) Result
    }
    class HealthGoalService
    class HealthPlanService {
        +generate(user, date) HealthPlan
        +today(user, generate) HealthPlan
        +complete(task) HealthPlan
        +uncomplete(task) HealthPlan
    }
    class HealthPlanGenerator
    class HealthPlanProgressService
    class NotificationService
    class NotificationPreferenceService
    class ReminderService
    class AuditLogService

    BmiRecordService --> BmiCalculator
    MealItemService --> MealService
    MealService --> NutritionSummaryService
    FoodAnalysisService --> FoodAnalysisProviderInterface
    HealthAssistantService --> HealthAIProviderInterface
    HealthAssistantService --> MedicalSafetyService
    SymptomAssessmentService --> EmergencyDetectionService
    SymptomAssessmentService --> HealthAIProviderInterface
    SymptomAssessmentService --> MedicalSafetyService
    HealthInsightsService --> HealthAIProviderInterface
    HealthInsightsService --> MedicalSafetyService
    HealthGoalService ..> BmiRecordService
    HealthPlanService --> HealthPlanGenerator
    HealthPlanService --> HealthPlanProgressService
    HealthPlanGenerator ..> HealthGoalService
    ReminderService --> NotificationService
    ReminderService --> NotificationPreferenceService
    NotificationService --> NotificationPreferenceService
```

## Aturan integritas class/domain yang penting

| Aturan | Implementasi |
| --- | --- |
| Satu profil kesehatan per user | Relasi `User 1 — 0..1 HealthProfile` dan unique `user_id`. |
| Satu profil nutrisi/preferensi notifikasi per user | Relasi has-one dan unique `user_id`. |
| Satu health plan per user per tanggal | Unique gabungan `user_id + plan_date`; generator bersifat idempoten. |
| Total meal dan progress plan tidak dipercaya dari client | Nilai dihitung ulang oleh service. |
| Kepemilikan child record | `HealthJournalEntry` melalui jurnal, `MealItem` melalui meal, `HealthPlanTask` melalui plan. |
| Draf food analysis bukan meal | `FoodAnalysis` tidak memiliki FK langsung ke `Meal`; service baru membuat meal saat konfirmasi valid. |
| Assessment ke jurnal opsional | `health_journal_id` nullable dan hanya diisi setelah konfirmasi. |
| Notifikasi durable lebih utama dari real-time | Record `UserNotification` dibuat dahulu; event broadcast adalah efek tambahan. |
| Audit tidak menyimpan konten kesehatan | `safe_metadata` dibatasi; IP di-hash; subject menggunakan relasi polymorphic. |

