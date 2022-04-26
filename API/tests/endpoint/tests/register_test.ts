import got from 'got';

Feature("register as new user");

Scenario("Get store order by id", async({I}) => {
    const resp = await got<any>('${process.env.BASE_URL}/v1/register')
});