<?php

namespace App\Http\Controllers\Admin;

use App\Expense;
use App\Http\Controllers\Controller;
use App\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpenseReportController extends Controller
{
    public function index()
    {
        $from = Carbon::parse(sprintf(
            '%s-%s-01',
            request()->query('y', Carbon::now()->year),
            request()->query('m', Carbon::now()->month)
        ));
        $to = clone $from;
        $to->day = $to->daysInMonth;

        $expenses = Expense::with('expense_category')
            ->whereBetween('entry_date', [$from, $to]);

        $incomes = Income::with('income_category')
            ->whereBetween('entry_date', [$from, $to]);

        $expensesTotal = $expenses->sum('amount');
        $incomesTotal = $incomes->sum('amount');
        $groupedExpenses = $expenses->whereNotNull('expense_category_id')->orderBy('amount', 'desc')->get()->groupBy('expense_category_id');
        $groupedIncomes = $incomes->whereNotNull('income_category_id')->orderBy('amount', 'desc')->get()->groupBy('income_category_id');
        $profit = $incomesTotal - $expensesTotal;

        $expensesSummary = [];

        foreach ($groupedExpenses as $exp) {
            foreach ($exp as $line) {
                if (!isset($expensesSummary[$line->expense_category->name])) {
                    $expensesSummary[$line->expense_category->name] = [
                        'name' => $line->expense_category->name,
                        'amount' => 0,
                    ];
                }

                $expensesSummary[$line->expense_category->name]['amount'] += $line->amount;
            }
        }

        $incomesSummary = [];

        foreach ($groupedIncomes as $inc) {
            foreach ($inc as $line) {
                if (!isset($incomesSummary[$line->income_category->name])) {
                    $incomesSummary[$line->income_category->name] = [
                        'name' => $line->income_category->name,
                        'amount' => 0,
                    ];
                }

                $incomesSummary[$line->income_category->name]['amount'] += $line->amount;
            }
        }

        return view('admin.expenseReports.index', compact(
           'expensesSummary',
            'incomesSummary',
            'expensesTotal',
            'incomesTotal',
            'profit'
        ));
    }


    public function chart()
{
    $year = request()->query('y', Carbon::now()->year);
    $from = Carbon::createFromDate($year, 1, 1);
    $to = Carbon::createFromDate($year, 12, 31);
    // $month1 = Carbon::now()->month;
    // $var = $month1->format('m');
    // dd($month1);

    // Initialize arrays for monthly income and expenses
    $monthlyIncome = array_fill(1, 12, 0);
    $monthlyExpenses = array_fill(1, 12, 0);

    // Fetching incomes
    $incomes = Income::whereBetween('entry_date', [$from, $to])->get();
    foreach ($incomes as $income) {
        // dd($income->entry_date);
        if($income->entry_date) {

            $month = $income->entry_date->month;

            $monthlyIncome[$month] += $income->amount;
        }
    }

    // Fetching expenses
    $expenses = Expense::whereBetween('entry_date', [$from, $to])->get();
    foreach ($expenses as $expense) {
        // dd($expense->entry_date);
        $date = $expense->entry_date;
        $month = Carbon::parse($date);
        if ($expense->entry_date) {
            $month = $month->month;
            $monthlyExpenses[$month] += $expense->amount;
        }
    }

    return response()->json([
        'months' => array_map(function ($month) {
            return Carbon::create()->month($month)->format('F');
        }, array_keys($monthlyIncome)),
        'income' => array_values($monthlyIncome),
        'expenses' => array_values($monthlyExpenses),
    ]);
}

}
